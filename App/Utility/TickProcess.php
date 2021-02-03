<?php


namespace App\Utility;


use App\Model\Document\Doc;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;
use EasySwoole\DocSystem\DocLib\DocSearchParser;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\Trigger\Trigger;
use Swoole\Coroutine;
use Swoole\Coroutine\System;

class TickProcess extends AbstractProcess
{
    protected function run($arg)
    {
        go(function () {
            while (1) {
                $list = Config::getInstance()->getConf("DOC.ALLOW_LANGUAGE");
                try {
                    foreach ($list as $dir => $value) {
                        $json = DocSearchParser::parserDoc2JsonUrlMap(EASYSWOOLE_ROOT, "{$dir}");
                        file_put_contents(EASYSWOOLE_ROOT . "/Static/keyword{$dir}.json", json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    }
                } catch (\Throwable $throwable) {
                    Trigger::getInstance()->throwable($throwable);
                }
            }
        });


        Timer::getInstance()->loop(30 * 1000, function () {
            //本项目是git克隆下来的，因此自动同步
            $exec = "cd " . EASYSWOOLE_ROOT . "; git pull";
            System::exec($exec);
        });
    }
}