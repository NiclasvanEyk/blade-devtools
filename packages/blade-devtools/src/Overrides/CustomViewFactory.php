<?php

namespace NiclasvanEyk\BladeDevtools\Overrides;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\Factory;
use Illuminate\View\View;
use NiclasvanEyk\BladeDevtools\Adapter\ViewFactoryDevtools;
use NiclasvanEyk\BladeDevtools\Paths\RemotePath;

class CustomViewFactory extends Factory
{
    private ViewFactoryDevtools $devtools;

    private ?ViewContract $currentlyRenderedRootView = null;

    public function setDevtools(ViewFactoryDevtools $devtools)
    {
        $this->devtools = $devtools;
    }

    public function devtools(): ViewFactoryDevtools
    {
        return $this->devtools;
    }

    public function flushState()
    {
        parent::flushState();

        $this->currentlyRenderedRootView = null;

        $this->devtools->flushState();
    }

    // Overriden to know which view originally triggered the rendering process
    public function callComposer(ViewContract $view)
    {
        parent::callComposer($view);

        if (! $this->currentlyRenderedRootView) {
            $this->currentlyRenderedRootView = $view;
        }
    }

    public function renderComponent()
    {
        $view = array_pop($this->componentStack);

        $this->currentComponentData = array_merge(
            $previousComponentData = $this->currentComponentData,
            $data = $this->componentData()
        );

        try {
            $view = value($view, $data);

            $content = '';
            $name = 'unknown';
            if ($view instanceof \Illuminate\View\View) {
                $content .= $view->with($data)->render();
                $context = $this->devtools->renderingContext()->currentComponentContext();
                $context->file = new RemotePath($view->getPath());
                $name = $view->getName();
            } elseif ($view instanceof Htmlable) {
                $content .= $view->toHtml();
                $name = $data['label'] ?? $view::class;
            } else {
                /** @var View */
                $v = $this->make($view, $data);
                $context = $this->devtools->renderingContext()->currentComponentContext();
                $context->file = new RemotePath($v->getPath());
                $content .= $v->render();
                $name = $v->getName();
            }

            // This is mostly the contribution by this library.
            // All code in here is copied from Laravel's source,
            // we only add markers to track the rendering process.
            return $this->withComponentMarkers($content, $name);
        } finally {
            $this->currentComponentData = $previousComponentData;
        }

        return $content;
    }

    private function withComponentMarkers(string $content, string $name): string
    {
        $context = $this->devtools->renderingContext();
        $component = $context->currentComponentContext();
        $component->view = $name;

        $w = "<!-- BLADE_COMPONENT_START[$component->id] -->\n";
        $w .= $content;
        $w .= "\n<!-- BLADE_COMPONENT_END[$component->id] -->";

        $context->closeCurrentComponentContext();

        return $w;
    }
}
