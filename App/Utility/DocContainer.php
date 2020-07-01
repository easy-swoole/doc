<?php


namespace App\Utility;


use App\Model\Document\Doc;
use EasySwoole\Component\Singleton;

class DocContainer
{
    protected $container = [];

    use Singleton;

    function add(Doc $doc)
    {
        $this->container[$doc->getName()] = $doc;
    }

    function get(string $name):?Doc
    {
        if(isset($this->container[$name])){
            return $this->container[$name];
        }else{
            return null;
        }
    }
}