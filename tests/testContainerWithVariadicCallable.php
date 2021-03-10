<?php

/**
 * Test class from Yii test suite.
 */

use Bizley\DeepInstantiate\Container;
use Bizley\Tests\YiiModels\QuxInterface;

$container = new Container();
$func = function (QuxInterface ...$quxes) {
    return "That's a whole lot of quxes!";
};
$container->invoke($func);
