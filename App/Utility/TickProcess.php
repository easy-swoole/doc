<?php


namespace App\Utility;


use App\Model\Document\Doc;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Trigger;
use Swoole\Coroutine;
use voku\helper\HtmlDomParser;

class TickProcess extends AbstractProcess
{

    protected function run($arg)
    {
        Timer::getInstance()->loop(30 * 1000, function () {
            $list = DocContainer::getInstance()->all();

            try {
                /** @var Doc $doc */
                foreach ($list as $doc) {
                    //定时生成搜索的json文件
                    $markdownResult = $doc->renderMarkdown('sidebar.md');
                    $dom = HtmlDomParser::str_get_html($markdownResult->getHtml());
                    $aList = $dom->find('a');

                    $jsonList = [];
                    $id = 1;
                    foreach ($aList as $a) {
                        $path = $a->href;
                        $name = $a->getNode()->textContent;

                        $link = $path;
                        if (substr($link, -3) == '.md') {
                            $link = substr($path, 0, -3) . '.html';
                        }

                        $jsonList[] = [
                            'id' => $id,
                            'title' => strtolower($name),
                            'content' => strip_tags($doc->renderMarkdown($path)->getHtml()),
                            'link' => '/' . $link,
                        ];
                        $id++;
                    }
                    file_put_contents(EASYSWOOLE_ROOT . "/Static/keyword{$doc->getName()}.json", json_encode($jsonList, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                }
            } catch (\Throwable $throwable) {
                Trigger::getInstance()->throwable($throwable);
            }

            //本项目是git克隆下来的，因此自动同步
            $exec = "cd " . EASYSWOOLE_ROOT . "; git pull";
            exec($exec);
            Logger::getInstance()->log('git sync');
        });
    }
}