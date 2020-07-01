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
        $doc = DocContainer::getInstance()->get('cn');
        if($doc instanceof Doc){
            $path = $this->request()->getUri()->getPath();
            if(substr($path,-5) == '.html'){
                $path = substr($path,0,-5);
            }
            $content = null;
            if(in_array($path,['/','/index'])){
                $content = $doc->displayHomePage();
            }else{
                $content = $doc->displayContentPage($path.".md");
            }
            if($content === null){
                $this->response()->withStatus(404);
                $content = $doc->displayPageNotFound();
            }else{
                $this->response()->withStatus(200);
            }
            $this->response()->withAddedHeader('Content-type',"text/html;charset=utf-8");
            if($content){
                $this->response()->write($content);
            }
        }else{
            $this->response()->write('not language match');
        }
    }
}