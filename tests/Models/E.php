<?php

declare(strict_types=1);

namespace Bizley\Tests\Models;

class E
{
    public $f;

    public $prop;

    public function __construct(F $f)
    {
        $this->f = $f;
    }
}
