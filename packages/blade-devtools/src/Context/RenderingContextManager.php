<?php

namespace NiclasvanEyk\BladeDevtools\Context;

final class RenderingContextManager
{
    private ?RenderingContext $current = null;

    public function current(): ?RenderingContext
    {
        return $this->current;
    }

    public function new(): RenderingContext
    {
        $this->current = new RenderingContext();

        return $this->current;
    }

    public function currentOrNew(): RenderingContext
    {
        if (!$this->current) {
            return $this->new();
        }

        return $this->current;
    }
}