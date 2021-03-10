<?php

namespace Bizley\Tests\YiiModels;

/**
 * Test class from Yii test suite.
 */
class Animal extends ActiveRecord
{
    public $does;

    public static function tableName()
    {
        return 'animal';
    }

    public function init()
    {
        parent::init();
        $this->type = \get_called_class();
    }

    public function getDoes()
    {
        return $this->does;
    }

    public static function instantiate($row)
    {
        $class = $row['type'];
        return new $class();
    }
}
