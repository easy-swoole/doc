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
            //本项目是git克隆下来的，因此自动同步
            $exec = "cd " . EASYSWOOLE_ROOT . "; git pull";
            Swoole\Coroutine\System::exec($exec);
            Logger::getInstance()->log('git sync');
        });
    }
}