<?php

namespace Bizley\Tests\YiiModels;

use yii\base\BaseObject;

/**
 * Test class from Yii test suite.
 */
class Qux extends BaseObject implements QuxInterface
{
    public $a;

    public function __construct($a = 1, $config = [])
    {
        $this->a = $a;
        parent::__construct($config);
    }

    public function quxMethod()
    {
    }
}
