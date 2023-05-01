<?php

namespace NiclasvanEyk\BladeDevtools\Adapter;

use function json_encode;
use NiclasvanEyk\BladeDevtools\Context\RenderingContext;

class BrowserDevtools
{
    public function toScript(RenderingContext $context)
    {
        $state = json_encode($context->serialize());

        return <<<SCRIPT
        <script id=\"blade-devtools-data\">
            window.__BDT_CONTEXT = JSON.parse($state);
        </script>
        SCRIPT;
    }
}