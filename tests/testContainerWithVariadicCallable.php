<?php

/**
 * Test class from Yii test suite.
 */

use bizley\deepinstantiate\Container;
use bizley\tests\yiimodels\QuxInterface;

$container = new Container();
$func = function (QuxInterface ...$quxes) {
    return "That's a whole lot of quxes!";
};
$container->invoke($func);
