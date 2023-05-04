<?php

namespace NiclasvanEyk\BladeDevtools\OpenInEditor;

final class EditorLinkGenerator
{
    public function __construct(
        public readonly string $editor,
        private readonly string $template,
    ) {
    }

    public function url(string $file, string $line): string
    {
        return str_replace(['%file', '%line'], [$file, $line], $this->template);
    }
}