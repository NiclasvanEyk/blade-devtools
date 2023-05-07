<?php

namespace NiclasvanEyk\BladeDevtools\Adapter;

use Illuminate\Support\Arr;
use Illuminate\View\Component;
use NiclasvanEyk\BladeDevtools\Context\RenderingContext;
use NiclasvanEyk\BladeDevtools\Context\RenderingContextManager;

/**
 * The "public interface" to the `__$env` variable / ViewFactory inside
 * compiled blade views.
 */
class ViewFactoryDevtools
{
    public function __construct(
        private readonly RenderingContextManager $manager,
    ) {
    }

    public function renderingContext(): RenderingContext
    {
        return $this->manager->currentOrNew();
    }

    public function setCurrentComponent(Component $component): void
    {
        $context = $this->renderingContext()->createComponentContext();
        $context->tag = $component->componentName;
        $context->data = Arr::except($component->data(), 'attributes');
        ksort($context->data);
        $context->class = $component::class;
    }

    public function flushState(): void
    {
        // TODO
    }
}
