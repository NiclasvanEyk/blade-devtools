<?php

namespace NiclasvanEyk\BladeDevtools\Adapter;

use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use NiclasvanEyk\BladeDevtools\Context\RenderingContext;

/**
 * The "public interface" to the `__$env` variable / ViewFactory inside
 * compiled blade views.
 */
class ViewFactoryDevtools
{
    private ?RenderingContext $renderingContext = null;

    public function renderingContext(): RenderingContext
    {
        $context = $this->renderingContext;

        if (!$context) {
            $context = new RenderingContext();
            $this->renderingContext = $context;
        }

        return $context;
    }

    public function setCurrentComponent(Component $component): void
    {
        $context = $this->renderingContext()->createComponentContext();
        $context->tag = $component->componentName;
        $context->data = $component->data();
        $context->class = $component::class;
    }

    public function flushState(): void
    {

        $this->renderingContext = null;
    }
}