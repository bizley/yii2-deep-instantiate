<?php

namespace Bizley\Tests\YiiModels;

/**
 * Test class from Yii test suite.
 */
class TraversableObject implements \Iterator, \Countable
{
    protected $data;
    private $position = 0;

    public function __construct(array $array)
    {
        $this->data = $array;
    }

    public function count()
    {
        throw new \Exception('Count called on object that should only be traversed.');
    }

    public function current()
    {
        return $this->data[$this->position];
    }

    public function next()
    {
        $this->position++;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return array_key_exists($this->position, $this->data);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
