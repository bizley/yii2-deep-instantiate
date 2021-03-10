<?php

declare(strict_types=1);

namespace Bizley\Tests\Models;

class A
{
    public $b;

    public function __construct(B $b)
    {
        $this->b = $b;
    }
}
