<?php


namespace App\Model\Document;


class Args
{
    protected $args = [];

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    function setArg($key,$val)
    {
        $this->args[$key] = $val;
    }

    function getArg($key)
    {
        if(isset($this->args[$key])){
            return $this->args[$key];
        }
        return null;
    }
}