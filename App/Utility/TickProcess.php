<?php


namespace App\Utility;


use App\Model\Document\Doc;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use EasySwoole\DocSystem\DocLib\DocSearchParser;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Trigger;
use Swoole\Coroutine;

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
                    $json = DocSearchParser::parserDoc2JsonUrlMap($doc->getRootPath() . '/', '');
                    file_put_contents(EASYSWOOLE_ROOT . "/Static/keyword{$doc->getName()}.json", json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                }
            }catch (\Throwable $throwable){
                Trigger::getInstance()->throwable($throwable);
            }

            //本项目是git克隆下来的，因此自动同步
            $exec = "cd " . EASYSWOOLE_ROOT . "; git pull";
            exec($exec);
            Logger::getInstance()->log('git sync');
        });
    }
}