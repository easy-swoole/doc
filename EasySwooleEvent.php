<?php
namespace EasySwoole\EasySwoole;


use App\Model\Document\Doc;
use App\Utility\DocContainer;
use App\Utility\TickProcess;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // TODO: Implement initialize() method.
        date_default_timezone_set('Asia/Shanghai');
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $cn = new Doc(EASYSWOOLE_ROOT.'/Cn');
        $cn->setName('ES_DOC_CN');
        $cn->getTemplate()->setHomePageTpl('index.tpl');
        $cn->getTemplate()->setSideBarMd('sideBar.md');
        $cn->getTemplate()->setContentPageTpl('contentPage.tpl');
        $cn->getTemplate()->setPageNotFoundTpl('404.tpl');
        DocContainer::getInstance()->add($cn);

        $swooleDoc = new Doc(EASYSWOOLE_ROOT.'/SwooleDoc');
        $swooleDoc->setName('SWOOLE_DOC');
        $swooleDoc->getTemplate()->setHomePageTpl('index.tpl');
        $swooleDoc->getTemplate()->setSideBarMd('sideBar.md');
        $swooleDoc->getTemplate()->setContentPageTpl('contentPage.tpl');
        $swooleDoc->getTemplate()->setPageNotFoundTpl('404.tpl');
        DocContainer::getInstance()->add($swooleDoc);

        $en = new Doc(EASYSWOOLE_ROOT.'/En');
        $en->setName('ES_DOC_EN');
        $en->getTemplate()->setHomePageTpl('index.tpl');
        $en->getTemplate()->setSideBarMd('sideBar.md');
        $en->getTemplate()->setContentPageTpl('contentPage.tpl');
        $en->getTemplate()->setPageNotFoundTpl('404.tpl');
        DocContainer::getInstance()->add($en);

        // Manager::getInstance()->addProcess(new TickProcess());
        self::initSearch();
    }

    private static function initSearch()
    {
        $scheduler = new \Swoole\Coroutine\Scheduler();
        $scheduler->add(function() {
            go(function () {
                // 写入搜索内容json
                $list = Config::getInstance()->getConf("DOC.ALLOW_LANGUAGE");
                try {
                    foreach ($list as $dir => $value) {
                        $json = DocSearchParser::parserDoc2JsonUrlMap(EASYSWOOLE_ROOT, "{$dir}");
                        file_put_contents(EASYSWOOLE_ROOT . "/Static/keyword{$dir}.json", json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                    }
                } catch (\Throwable $throwable) {
                    \EasySwoole\EasySwoole\Trigger::getInstance()->throwable($throwable);
                }
            });
        });
        $scheduler->start();
        // 清除全部定时器
        \Swoole\Timer::clearAll();
    }
}
