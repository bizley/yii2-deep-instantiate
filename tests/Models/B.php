<?php

declare(strict_types=1);

namespace Bizley\Tests\Models;

class B
{
    public $c;

    public function __construct(C $c)
    {
        $this->c = $c;
    }
}
