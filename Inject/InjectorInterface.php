<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 16/7/25
 * Time: 下午5:42
 */

namespace Injector;


interface InjectorInterface
{

    public function map($k,$v,$c);

    public function get($k);

    public function build($k,$params = array());

}