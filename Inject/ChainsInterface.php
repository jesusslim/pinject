<?php
/**
 * Created by PhpStorm.
 * User: jesusslim
 * Date: 2017/5/26
 * Time: 下午2:01
 */

namespace Inject;


interface ChainsInterface
{

    public function chain($handlers);

    public function data($data);

    public function run();
}