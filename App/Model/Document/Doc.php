<?php


namespace App\Model\Document;


use EasySwoole\EasySwoole\Trigger;
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

    function displayHomePage(?Args $args = null):?string
    {
        return $this->render($this->template->getHomePageTpl(),$args);
    }

    function displayContentPage(string $mdFile,?Args $args = null):?string
    {
        return $this->render($mdFile,$args);
    }

    function renderMarkdown(string $mdFile)
    {
        $mdFile = $this->rootPath.$mdFile;
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

    function displayPageNotFound(?Args $args = null):?string
    {
        return $this->render($this->template->getPageNotFoundTpl(),$args);
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
        $file = $this->rootPath.$file;
        if(!file_exists($file)){
            return null;
        }
        $args->setArg("DOC_NAME",$this->name);
        $sidebar = file_get_contents($this->rootPath.$this->template->getSideBarMd());
        $sidebar = (new ParserDown())->parse($sidebar);
        $args->setArg("SIDE_BAR",$sidebar);
        return file_get_contents($file);
    }
}