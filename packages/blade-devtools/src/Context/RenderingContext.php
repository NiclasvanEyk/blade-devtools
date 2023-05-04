<?php

namespace NiclasvanEyk\BladeDevtools\Context;

use NiclasvanEyk\BladeDevtools\ComponentDataSerializer;
use function json_encode;

/**
 * Holds data generated during a rendering pass of a top-level view.
 */
class RenderingContext
{
    /**
     * @var array<string,ComponentContext>
     */
    public array $components = [];

    private ?ComponentContext $currentComponentContext = null;

    public function currentComponentContext(): ComponentContext
    {
        $context = $this->currentComponentContext;

        if (!$context) {
            $context = $this->createComponentContext();
        }

        return $context;
    }

    public function createComponentContext(): ComponentContext
    {
        $context = new ComponentContext(
            parent: $this->currentComponentContext
        );
        $this->components[$context->id] = $context;
        $this->currentComponentContext = $context;

        return $context;
    }

    public function closeCurrentComponentContext(): void
    {
        if ($this->currentComponentContext) {
            $this->currentComponentContext = $this->currentComponentContext->parent;
        } else {
            $this->currentComponentContext = null;
        }
    }
}