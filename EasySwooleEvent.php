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
        $cn->setName('cn');
        $cn->getTemplate()->setHomePageTpl('index.tpl');
        $cn->getTemplate()->setSideBarMd('sideBar.md');
        $cn->getTemplate()->setContentPageTpl('contentPage.tpl');
        DocContainer::getInstance()->add($cn);

    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}