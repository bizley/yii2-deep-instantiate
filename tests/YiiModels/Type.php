<?php

namespace Bizley\Tests\YiiModels;

/**
 * Test class from Yii test suite.
 */
class Type extends ActiveRecord
{
    public $name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'type';
    }
}
