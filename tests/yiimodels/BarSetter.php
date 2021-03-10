<?php

namespace bizley\tests\yiimodels;

use yii\base\BaseObject;

/**
 * Test class from Yii test suite.
 */
class BarSetter extends BaseObject
{
    private $qux;

    public function getQux()
    {
        return $this->qux;
    }

    public function setQux(QuxInterface $qux)
    {
        $this->qux = $qux;
    }
}
