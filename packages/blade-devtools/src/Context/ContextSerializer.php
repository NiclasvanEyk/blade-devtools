<?php

namespace NiclasvanEyk\BladeDevtools\Context;

use NiclasvanEyk\BladeDevtools\ComponentDataSerializer;
use NiclasvanEyk\BladeDevtools\OpenInEditor\EditorLinkGenerator;
use NiclasvanEyk\BladeDevtools\Paths\PathConverter;
use function json_encode;

final class ContextSerializer
{
    public function __construct(
        private readonly PathConverter $pathConverter,
        private readonly ComponentDataSerializer $dataSerializer,
        private readonly EditorLinkGenerator $editorLinkGenerator,
    ) {
    }

    public function serialize(RenderingContext $context): string
    {
        // The data of each component should be serialized to a JSON-string.
        // The devtools UI can then interpret/consume said string.

        $components = [];
        foreach ($context->components as $component) {
            $components[$component->id] = [
                'id' => $component->id,
                'data' => $this->dataSerializer->serialize($component->data),
                'tag' => $component->tag,
                'view' => $component->view,
            ];

            if ($file = $component->file) {
                $components[$component->id]['file'] = $this->pathConverter->projectRelative($file)->path;
                $components[$component->id]['file_open_url'] = $this->editorLinkGenerator->url(
                    $this->pathConverter->local($file)->path,
                    "0",
                );
            }
        }

        return json_encode($components);
    }

    public function toScriptTag(RenderingContext $context): string
    {
        return '<script id="blade-devtools-component-data">'
            . 'window.__BLADE_DEVTOOLS_COMPONENT_DATA = JSON.parse('
            . json_encode($this->serialize($context))
            . ');'
            . '</script>';
    }
}