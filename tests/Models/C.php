<?php

declare(strict_types=1);

namespace Bizley\Tests\Models;

class C
{
    public $d;

    public function __construct(D $d)
    {
        $this->d = $d;
    }
}
