<?php
namespace EasySwoole\EasySwoole;


use App\Model\Document\Doc;
use App\Utility\DocContainer;
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
    }
}