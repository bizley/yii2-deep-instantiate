<?php

namespace Bizley\Tests\YiiModels;

use yii\base\BaseObject;

/**
 * Test class from Yii test suite.
 */
class Corge extends BaseObject
{
    public $map;

    public function __construct(array $map, $config = [])
    {
        $this->map = $map;
        parent::__construct($config);
    }
}
