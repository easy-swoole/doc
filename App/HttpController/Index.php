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
        $doc = $this->request()->getRequestParam('doc');
        if(empty($doc)){
            $doc = 'ES_DOC_CN';
        }
        $doc = DocContainer::getInstance()->get($doc);
        if($doc instanceof Doc){
            $doc->display($this->request(),$this->response());
        }else{
            $this->response()->write('not language match');
        }
    }
}
