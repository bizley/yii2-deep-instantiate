<?php

namespace Bizley\Tests\YiiModels;

use yii\base\BaseObject;

/**
 * Test class from Yii test suite.
 */
class Bar extends BaseObject
{
    public $qux;

    public function __construct(QuxInterface $qux, $config = [])
    {
        $this->qux = $qux;
        parent::__construct($config);
    }
}
