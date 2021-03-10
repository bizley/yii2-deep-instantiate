<?php

namespace Bizley\Tests\YiiModels;

/**
 * Test class from Yii test suite.
 */
class ActiveRecord extends \yii\db\ActiveRecord
{
    public static $db;

    public static function getDb()
    {
        return self::$db;
    }
}
