<?php

namespace bizley\tests\yiimodels;

/**
 * Test class from Yii test suite.
 */
class Cat extends Animal
{
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $record->does = 'meow';
    }

    public function getException()
    {
        throw new \Exception('no');
    }

    public function getThrowable()
    {
        return 5/0;
    }
}
