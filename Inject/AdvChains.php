<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 2017/5/26
 * Time: 下午1:48
 */

namespace Inject;

use Closure;

class AdvChains implements ChainsInterface
{

    /** @var  \Inject\InjectorInterface $context */
    protected $context;
    protected $req;
    protected $handlers;
    protected $delimiter;

    public function __construct($context,$delimiter = '|')
    {
        $this->context = $context;
        $this->handlers = [];
        $this->delimiter = $delimiter;
        return $this;
    }

    public function data($data){
        $this->req = $data;
        return $this;
    }

    public function chain($handlers = array()){
        $this->handlers = array_merge($this->handlers,is_array($handlers) ? $handlers : func_get_args());
        return $this;
    }

    public function run(){
        $result = null;
        foreach ($this->handlers as $index => $handler){
            if ($handler instanceof Closure){
                $result = $this->context->call($handler,$this->req);
            }else{
                $str = explode($this->delimiter,$handler);
                if (count($str) < 2) throw new AdvChainsException('Invalid handler');
                $result = $this->context->callInClass($str[0],$str[1],$this->req);
            }
        }
        return $result;
    }

}