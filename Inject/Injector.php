<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 16/7/25
 * Time: 下午5:44
 */

namespace Inject;

use Closure;
use ReflectionClass;
use ReflectionFunction;
class Injector implements InjectorInterface
{

    const INDEX_CONCRETE = 0;
    const INDEX_CACHED = 1;
    protected $objects = [];
    protected $caches = [];
    protected $data = [];

    public function map($key,$obj = null,$need_cache = false){
        $this->clearCache($key);
        if (is_null($obj)) {
            $obj = $key;
        }
        $this->objects[$key] = array_values(compact('obj','need_cache'));
        return $this;
    }

    public function mapData($key,$data){
        $this->data[$key] = $data;
    }

    public function mapSingleton($key,$class = null){
        return $this->map($key,$class,true);
    }

    public function get($key){
        if(isset($this->objects[$key])){
            return $this->objects[$key];
        }
        throw new InjectorException("obj $key not found");
    }

    public function clearCache($key){
        unset($this->caches[$key]);
    }

    public function getData($key){
        if(isset($this->data[$key])){
            return $this->data[$key];
        }
        throw new InjectorException("data $key not found");
    }

    public function getCache($key){
        return isset($this->caches[$key]) ? $this->caches[$key] : null;
    }

    public function produce($key,$params = array()){
        //if in data
        if(isset($this->data[$key])) return $this->data[$key];
        //if cached
        if(isset($this->caches[$key])) return $this->caches[$key];
        $obj = $this->get($key);
        $concrete = $obj[self::INDEX_CONCRETE];
        $result = $this->build($concrete,$params);
        if($obj[self::INDEX_CACHED] === true){
            $this->caches[$key] = $result;
        }
        return $result;
    }

    public function build($concrete,$params = array()){
        //if closure
        if($concrete instanceof Closure){
            return $concrete($this,$params);
        }
        //reflect
        $ref = new ReflectionClass($concrete);
        if(!$ref->isInstantiable()) throw new InjectorException("$concrete is not instantiable");
        $constructor = $ref->getConstructor();
        if(is_null($constructor)) return new $concrete;
        //constructor
        $params_in_constructor = $constructor->getParameters();
        $args = $this->apply($params_in_constructor,$params);
        return $ref->newInstanceArgs($args);
    }

    public function apply(array $params,$value_given = array()){
        $result = array();
        foreach ($params as $param){
            if(key_exists($param->name,$value_given)){
                $result[] = $value_given[$param->name];
            }else{
                $class = $param->getClass();
                $name_to_produce = is_null($class) ? $param->name : $class->name;
                try{
                    $temp = $this->produce($name_to_produce);
                }catch (InjectorException $e){
                    if($param->isDefaultValueAvailable()){
                        $temp = $param->getDefaultValue();
                    }else{
                        throw $e;
                    }
                }
                $result[] = $temp;
            }
        }
        return $result;
    }

    public function call(Closure $c,$params = array()){
        $ref = new ReflectionFunction($c);
        $params_need = $ref->getParameters();
        $args = $this->apply($params_need,$params);
        return $ref->invokeArgs($args);
    }
}