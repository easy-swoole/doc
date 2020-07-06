<?php


namespace App\Model\Document;


use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ParserDown\ParserDown;
use voku\helper\HtmlDomParser;

class Doc
{
    protected $name = 'default';

    protected $template;

    protected $rootPath;

    function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        $this->template = new Template();
    }

    /**
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    protected function displayHomePage(?Args $args = null):?string
    {
        if(!file_exists($this->rootPath.'/'.$this->template->getHomePageTpl())){
            return null;
        }
        return $this->render($this->template->getHomePageTpl(),$args);
    }

    protected function displayContentPage(string $mdFile,?Args $args = null):?string
    {
        if(!file_exists($this->rootPath.$mdFile)){
            return null;
        }
        if(!$args){
            $args = new Args();
        }
        //侧边栏
        $sideBar= $this->renderMarkdown($this->template->getSideBarMd());
        if($sideBar){
            $args->setArg('sideBar',$sideBar->getHtml());
        }else{
            $args->setArg('sideBar',null);
        }
        //正文
        $args->setArg('markdownFile',$mdFile);
        $c = $this->renderMarkdown($mdFile);
        if($c){
            $c = $c->toArray();
        }else{
            $c = null;
        }
        $args->setArg('page',$c);
        return $this->render($this->getTemplate()->getContentPageTpl(),$args);
    }

    protected function displayPageNotFound(?Args $args = null):?string
    {
        if(!file_exists($this->rootPath.'/'.$this->template->getPageNotFoundTpl())){
            return null;
        }
        return $this->render($this->template->getPageNotFoundTpl(),$args);
    }

    function display(Request $request,Response $response):void
    {
        $path = $request->getUri()->getPath();
        $info = pathinfo($path);
        $path = $info['dirname'];
        if($info['filename'] != 'index'){
            $path = rtrim($path,'/')."/".$info['filename'];
        }
        if(empty($info['extension'])){
            $info['extension'] = 'html';
        }
        if($path == '/' || (isset($info['extension']) && $info['extension'] == 'html')){
            $response->withAddedHeader('Content-type',"text/html;charset=utf-8");
            if($path == '/'){
                $content = $this->displayHomePage();
            }else{
                $content = $this->displayContentPage($path.".md");
            }
            if($content === null){
                $response->withStatus(404);
                $content = $this->displayPageNotFound();
            }else{
                $response->withStatus(200);
            }
            if($content){
                $response->write($content);
            }
        }else{
            $response->withStatus(404);
        }
    }

    function renderMarkdown(string $mdFile)
    {
        $mdFile = $this->rootPath.'/'.$mdFile;
        if(file_exists($mdFile)){
            $result = new MarkDownResult();
            $content = '';
            $head = '';
            $file = fopen($mdFile, "r");
            $isInHead = false;
            while (is_resource($file) && !feof($file)) {
                $line = fgets($file);
                if ($isInHead) {
                    if (strlen(trim($line))==3 && substr($line, 0, 3) == '---') {
                        $isInHead = false;
                    } else {
                        $head = $head . $line;
                    }
                } else {
                    if (strlen(trim($line))==3 && substr($line, 0, 3) == '---') {
                        $isInHead = true;
                    } else {
                        $content = $content . $line;
                    }
                }
            }
            fclose($file);
            if(!empty($head)){
                $config = yaml_parse($head);
                if($config === false){
                    Trigger::getInstance()->error("yaml parse file {$mdFile} error");
                }else if(is_array($config)){
                    $result->setConfig($config);
                }
            }
            if(!empty($content)){
                $result->setHtml((new ParserDown())->parse($content));
                $dom = HtmlDomParser::str_get_html($result->getHtml());
                //删除代码标签
                foreach ($dom->find("code") as $code){
                    $code->innerhtml = '';
                }
                $result->setPlainText($dom->text());
            }
            return $result;
        }else{
            return null;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    protected function render(string $file,?Args $args = null)
    {
        if(!$args){
            $args = new Args();
        }
        $args->setArg("docName",$this->name);
        $temp = sys_get_temp_dir();
        $smarty = new \Smarty();
        $smarty->setTemplateDir($this->rootPath);
        $smarty->setCacheDir("{$temp}/smarty/cache/");
        $smarty->setCompileDir("{$temp}/smarty/compile/");
        foreach ($args->getArgs() as $key => $val){
            $smarty->assign($key,$val);
        }
        return $smarty->fetch($this->rootPath.'/'.$file);
    }
}