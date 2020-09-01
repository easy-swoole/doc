<?php


namespace App\Utility;


use App\Model\Document\Doc;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;

class TickProcess extends AbstractProcess
{

    protected function run($arg)
    {
        Timer::getInstance()->loop(30*1000,function (){
            $list = DocContainer::getInstance()->all();
            /** @var Doc $doc */
            foreach ($list as $doc){
                //定时生成搜索的json文件
            }
        });
    }
}