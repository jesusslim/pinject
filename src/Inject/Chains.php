<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 16/7/25
 * Time: 下午10:23
 */

namespace Injector;

use Closure;
class Chains
{

    protected $context;

    protected $handlers;

    protected $action;

    protected $req;

    public function __construct($context)
    {
        $this->context = $context;
        $this->handlers = [];
        $this->action = 'handle';
    }

    public function data($data){
        $this->req = $data;
        return $this;
    }

    public function chain($handlers = array()){
        $this->handlers = array_merge($this->handlers,is_array($handlers) ? $handlers : func_get_args());
        return $this;
    }

    public function action($action){
        $this->action = $action;
        return $this;
    }

    public function run(){
        $handlers_registered = $this->handlers;
        $last_handler = array_pop($handlers_registered);
        $last = function($data) use ($last_handler){
            return call_user_func($last_handler,$data);
        };
        $handlers = array_reverse($handlers_registered);
        return call_user_func(array_reduce($handlers,$this->walk(),$last),$this->req);
    }

    public function runWith(Closure $call_back){
        $last = function($data) use ($call_back){
            return call_user_func($call_back,$data);
        };
        $handlers = array_reverse($this->handlers);
        return call_user_func(array_reduce($handlers,$this->walk(),$last),$this->req);
    }

    protected function walk(){
        return function($next_cb,$func_now){
            return function($data) use ($next_cb,$func_now){
                if($func_now instanceof Closure){
                    return call_user_func($func_now,$data,$next_cb);
                }elseif (!is_object($func_now)){
                    $func_now = $this->context->produce($func_now);
                }
                $args = [$data,$next_cb];
                return call_user_func_array([$func_now,$this->action],$args);
            };
        };
    }
}