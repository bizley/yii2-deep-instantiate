<?php

namespace bizley\tests\yiimodels;

/**
 * Test class from Yii test suite.
 */
class Variadic
{
    public function __construct(QuxInterface ...$quxes)
    {
    }
}
