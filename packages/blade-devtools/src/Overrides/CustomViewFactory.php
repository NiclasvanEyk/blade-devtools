<?php

namespace NiclasvanEyk\BladeDevtools\Overrides;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Factory;
use Illuminate\View\View;
use NiclasvanEyk\BladeDevtools\Adapter\BrowserDevtools;
use NiclasvanEyk\BladeDevtools\Adapter\ViewFactoryDevtools;

class CustomViewFactory extends Factory
{
    private ?ViewFactoryDevtools $devtools = null;

    public function devtools(): ViewFactoryDevtools
    {
        $devtools = $this->devtools;

        if (! $devtools) {
            $devtools = new ViewFactoryDevtools();
            $this->devtools = $devtools;
        }

        return $devtools;
    }

    public function flushState()
    {
        parent::flushState();

        $this->devtools()->flushState();
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
                $name = $view->getName();
            } elseif ($view instanceof Htmlable) {
                $content .= $view->toHtml();
                $name = $data['label'] ?? $view::class;
            } else {
                /** @var View */
                $v = $this->make($view, $data);
                $content .= $v->render();
                $name = $v->getName();
            }

            // This is mostly the contribution by this library.
            // All code in here is copied from Laravel's source,
            // we only add markers to to track the rendering process.
            return $this->withComponentMarkers($content, data: $data, name: $name);
        } finally {
            $this->currentComponentData = $previousComponentData;
        }

        return $content;
    }

    private function withComponentMarkers(string $content, array $data, string $name): string
    {
        // No need to dump values twice
        // if ($data['attributes'] instanceof AttributeBag) {
        //     $data = $data['attributes'];
        // }

        if (array_key_exists('attributes', $data)) {
            $data = $data['attributes']->getAttributes();
        }
        unset($data['__laravel_slots']);
        unset($data['slot']);

        $context = $this->devtools()->renderingContext()->currentComponentContext();
        $context->view = $name;

        $this->devtools()->renderingContext()->closeCurrentComponentContext();

        $w = "<!-- BLADE_COMPONENT_START[$context->id] -->";
        $w .= $content;
        $w .= "<!-- BLADE_COMPONENT_END[$context->id] -->";

        if ($context->parent === null) {
            Log::info('[BDT] Done! rendering state...');
            $w .= (new BrowserDevtools())->toScript($this->devtools()->renderingContext());
        } else {
            $parent = $context->parent;
            Log::info('Not done yet!', ['parent' => ['id' => $parent->id, 'tag' => $parent->tag, 'view' => $parent->view]]);
        }

        return $w;
    }
}