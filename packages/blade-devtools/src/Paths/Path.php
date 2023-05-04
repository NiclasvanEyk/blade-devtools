<?php

namespace NiclasvanEyk\BladeDevtools\Paths;

use Stringable;

abstract class Path implements Stringable
{
    public function __construct(public readonly string $path)
    {
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
    }
}