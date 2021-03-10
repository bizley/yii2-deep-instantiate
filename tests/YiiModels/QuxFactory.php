<?php

namespace Bizley\Tests\YiiModels;

use Bizley\DeepInstantiate\Container;
use yii\base\BaseObject;

/**
 * Test class from Yii test suite.
 */
class QuxFactory extends BaseObject
{
    public static function create(Container $container)
    {
        return new Qux(42);
    }
}
