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

    public function serialize(): string
    {
        // The data of each component should be serialized to a JSON-string.
        // The devtools UI can then interpret/consume said string.

        $components = [];
        foreach ($this->components as $context) {
            $components[$context->id] = [
                'id' => $context->id,
                'data' => (new ComponentDataSerializer())->serialize($context->data),
                'tag' => $context->tag,
                'view' => $context->view,
                'file' => $context->file,
            ];
        }

        return json_encode($components);
    }

    public function serializeToScriptTag(): string
    {
        return '<script id="blade-devtools-component-data">'
            . 'window.__BLADE_DEVTOOLS_COMPONENT_DATA = JSON.parse('
                . json_encode($this->serialize())
            . ');'
            . '</script>';
    }
}