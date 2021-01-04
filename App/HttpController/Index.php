<?php


namespace App\HttpController;


use App\Model\Document\Doc;
use App\Utility\DocContainer;
use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{
    function index()
    {
        $this->actionNotFound('index');
    }

    protected function actionNotFound(?string $action)
    {
        $host = $this->request()->getUri()->getHost();
        switch ($host){
            case 'english.easyswoole.com':{
                $doc = 'SWOOLE_DOC';
                break;
            }
            default:{
                $doc = 'ES_DOC_CN';
                break;
            }
        }
        $doc = DocContainer::getInstance()->get($doc);
        if($doc instanceof Doc){
            $doc->display($this->request(),$this->response());
        }else{
            $this->response()->write('not language match');
        }
    }
}
