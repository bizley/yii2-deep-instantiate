<?php

namespace Bizley\Tests\YiiModels;

/**
 * Test class from Yii test suite.
 */
class Variadic
{
    public function __construct(QuxInterface ...$quxes)
    {
    }
}
