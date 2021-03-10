<?php

namespace bizley\tests\yiimodels;

use yii\base\BaseObject;

/**
 * Test class from Yii test suite.
 */
class Foo extends BaseObject
{
    public $bar;

    public function __construct(Bar $bar, $config = [])
    {
        $this->bar = $bar;
        parent::__construct($config);
    }
}
