---
title: easyswoole 内容检测
meta:
  - name: description
    content: easyswoole 内容检测
  - name: keywords
    content: swoole|easyswoole|内容检测|敏感词|检测
---

### 实时添加、移除的词，服务停止后怎么办？

> 1.x 版本服务停止时会将正在运行中的所有词落地到文件，2.x移除了这一特性
我们更倾向于用户自己处理这些词。举个例子：比如你所有的词都存在db中，在线添加移除词时可相应更新db，
然后定时去刷新词库文件。

### 如何做到游戏中"香词"变*？

> 检测结果中会有命中词在文章的具体位置，然后你再根据词的长度做相应的替换，或者你干脆直接替换命中的词，根据这个思路
可以实现更多好玩的事情。

### QQ会根据聊天内容下表情雨，这是怎么做到的？

> 检测聊天内容，命中相应关键词，拉取对应的表情扔到你屏幕上。

<img src="/Images/WordsMatch/qq.jpg" alt="图片替换文本" width="300" height="500" align="bottom" />


