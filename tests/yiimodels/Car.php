<?php

namespace bizley\tests\yiimodels;

use yii\base\BaseObject;

/**
 * Test class from Yii test suite.
 */
class Car extends BaseObject
{
    public $color;
    public $name;

    public function __construct($color, $name)
    {
        $this->color = $color;
        $this->name = $name;
    }
}
