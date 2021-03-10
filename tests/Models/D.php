<?php

declare(strict_types=1);

namespace Bizley\Tests\Models;

class D
{
    public $e;

    public function __construct(E $e)
    {
        $this->e = $e;
    }
}
