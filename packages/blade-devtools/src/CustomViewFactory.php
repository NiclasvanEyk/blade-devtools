<?php

namespace NiclasvanEyk\BladeDevtools;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\Factory;
use Illuminate\View\View;
use Ramsey\Uuid\Uuid;

class CustomViewFactory extends Factory
{
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
            if ($view instanceof \Illuminate\View\View) {
                $content .= $view->with($data)->render();
            } elseif ($view instanceof Htmlable) {
                $content .= $view->toHtml();
            } else {
                /** @var View */
                $v = $this->make($view, $data);
                $content .= $v->render();
            }

            // This is mostly the contribution by this library.
            // All code in here is copied from Laravel's source,
            // we only add markers to to track the rendering process.
            return $this->withComponentMarkers($content, data: $data);
        } finally {
            $this->currentComponentData = $previousComponentData;
        }

        return $content;
    }

    private function withComponentMarkers(string $content, array $data): string
    {
        $id = Uuid::uuid4();

        $data = json_encode($data);

        $w = "<!-- BLADE_COMPONENT_START[$id] -->";
        $w .= "<!-- BLADE_COMPONENT_DATA[$data] -->";
        $w .= $content;
        $w .= "<!-- BLADE_COMPONENT_END[$id] -->";

        return $w;
    }
}
