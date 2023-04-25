<?php

namespace NiclasvanEyk\BladeDevtools;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\Factory;
use Illuminate\View\View;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

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
        $id = Uuid::uuid4();

        // No need to dump values twice
        // if ($data['attributes'] instanceof AttributeBag) {
        //     $data = $data['attributes'];
        // }

        if (array_key_exists('attributes', $data)) {
            $data = $data['attributes']->getAttributes();
        }
        unset($data['__laravel_slots']);
        unset($data['slot']);

        $cloned = (new VarCloner())->cloneVar($data);
        $dumper = new HtmlDumper();
        $dumper->setTheme('light');

        $data = json_encode([
            'data' => $data,
            'data_dumped' => $dumper->dump($cloned, output: true),
            'data_serialized' => (new Serializer)->serialize($data),
            'name' => $name,
        ]);

        $w = "<!-- BLADE_COMPONENT_START[$id] -->";
        $w .= "<!-- BLADE_COMPONENT_DATA[$data] -->";
        $w .= $content;
        $w .= "<!-- BLADE_COMPONENT_END[$id] -->";

        return $w;
    }
}
