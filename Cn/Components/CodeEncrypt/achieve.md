---
title: easyswoole php源码加密原理
meta:
  - name: description
    content: easyswoole php源码加密原理
  - name: keywords
    content: php源码加密|swoole源码加密|easyswoole源码加密
---

# 实现原理
- 在拓展层实现代码加密，生成新代码
- 在拓展层解密代码
    - hook校验
    - opcode混淆
- 在拓展层执行解密后代码

# 知识储备
首先，对于一个php文件的执行，我们需要知道其大概的步骤：
- 基础环境初始化
- 调用zend_compile_file解析文件生成opcode
- 调用zend_execute执行生成的opcode

## 相关函数
```
static zend_op_array *(*zend_compile_string)(zval *source_string, char *filename TSRMLS_DC);
```
```
static zend_op_array *(*zend_compile_string)(zval *source_string, char *filename TSRMLS_DC);
```
```
static void zend_execute(zend_op_array *op_array,zval *return_value);
```

## 替换PHP默认方法

```
PHP_MINIT_FUNCTION(decrypt_code)
{
    zend_compile_file = decrypt_compile_file;
    orig_compile_string = zend_compile_string;
    zend_compile_string = decrypt_compile_string;
    return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(myShut)
{
    zend_compile_string = orig_compile_string;
    return SUCCESS;
}
```

我们在php加载拓展的时候，替换了php默认的 ```zend_compile_file```和```orig_compile_string```。当然，在Easyswoole中实现的执行代码的方式，
不会被这两个函数hook，这个两个可以用来破解纯php层的混淆加密。相关安全问题在注意事项章节讲解。

## 定义加密方法
```
PHP_FUNCTION(easy_compiler_encrypt) {
    unsigned char *raw_string;
    size_t *raw_string_len;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &raw_string, &raw_string_len) == FAILURE) {
        RETURN_NULL();
    }
    unsigned char *pkcs7 = (unsigned char *)malloc(sizeof(unsigned char*)*PKCS7_MAX_LEN);
    memcpy(pkcs7,raw_string,raw_string_len);
    size_t after_padding_len = PKCS7Padding(pkcs7,raw_string_len);

    struct AES_ctx ctx;
    AES_init_ctx_iv(&ctx, AES_KEY, AES_IV_KEY);
    AES_CBC_encrypt_buffer(&ctx,pkcs7,after_padding_len);
    zend_string *zend_encode_string = zend_string_init(pkcs7,after_padding_len,0);
    zend_string *base64;
    base64 = php_base64_encode((const unsigned char*)ZSTR_VAL(zend_encode_string),ZSTR_LEN(zend_encode_string));
    char *res = ZSTR_VAL(base64);
    zend_string_release(base64);
    zend_string_release(zend_encode_string);
    free(pkcs7);
    RETURN_STRING(res);
};
```

## 定义解密方法
```
PHP_FUNCTION(easy_compiler_decrypt) {
    unsigned char *base64;
    size_t *base64_len;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &base64, &base64_len) == FAILURE) {
        RETURN_NULL();
    }
    zend_string *encrypt_z_str;
    encrypt_z_str = php_base64_decode(base64,base64_len);
    size_t encrypt_len = NULL;
    encrypt_len = ZSTR_LEN(encrypt_z_str);
    unsigned char *pkcs7 = (unsigned char *)malloc(sizeof(unsigned char*)*PKCS7_MAX_LEN);
    memcpy(pkcs7,(const char*)ZSTR_VAL(encrypt_z_str),encrypt_len);
    struct AES_ctx ctx;
    AES_init_ctx_iv(&ctx, AES_KEY, AES_IV_KEY);
    AES_CBC_decrypt_buffer(&ctx,pkcs7,encrypt_len);
    encrypt_len = PKCS7Cutting(pkcs7,encrypt_len);
    zend_string *eval_string = zend_string_init(pkcs7,encrypt_len,0);
    zval z_str;
    ZVAL_STR(&z_str,eval_string);
    zend_op_array *new_op_array;
    char *filename = zend_get_executed_filename(TSRMLS_C);
    new_op_array =  easy_compiler_compile_string(&z_str, filename TSRMLS_C);
    if(new_op_array){
        zend_try {
            zend_execute(new_op_array,return_value);
        } zend_catch {

        } zend_end_try();
        destroy_op_array(new_op_array);
        efree(new_op_array);
    }
    zend_string_release(encrypt_z_str);
    zend_string_release(eval_string);
    zval_ptr_dtor(&z_str);
    free(pkcs7);
};
```
> 就是在这一步解析加密后的代码，并执行对应的opcode

## 更多细节源码

[EasySwoole Compiler](https://github.com/easy-swoole/compiler/blob/master/src/compiler.c)