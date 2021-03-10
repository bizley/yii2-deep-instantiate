<?php

namespace bizley\tests\yiimodels;

/**
 * Test class from Yii test suite.
 */
class Order extends ActiveRecord
{
    public static $tableName;

    public static function tableName()
    {
        return static::$tableName ?: 'order';
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->created_at = time();

            return true;
        }

        return false;
    }

    public function attributeLabels()
    {
        return [
            'customer_id' => 'Customer',
            'total' => 'Invoice Total',
        ];
    }

    public function activeAttributes()
    {
        return [
            0 => 'customer_id',
        ];
    }
}
