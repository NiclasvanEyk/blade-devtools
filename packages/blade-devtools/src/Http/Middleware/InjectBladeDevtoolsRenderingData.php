<?php

namespace NiclasvanEyk\BladeDevtools\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use NiclasvanEyk\BladeDevtools\Context\ContextSerializer;
use NiclasvanEyk\BladeDevtools\Context\RenderingContext;
use NiclasvanEyk\BladeDevtools\Context\RenderingContextManager;
use function strripos;
use function substr;

class InjectBladeDevtoolsRenderingData
{
    public function __construct(
        private readonly RenderingContextManager $renderingContext,
        private readonly ContextSerializer $serializer,
    ) {
    }

    public function handle($request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->getRequestFormat() !== 'html') {
            return $response;
        }

        $context = $this->renderingContext->current();
        if (!$context) {
            Log::info('No rendering context...');
            return $response;
        }

        Log::info("Adding script after closing body tag...");

        $this->inject($context, $response);

        return $response;
    }

    private function inject(RenderingContext $context, Response $response): void
    {
        $content = $response->getContent();
        $serializedContext = $this->serializer->toScriptTag($context);
        $pos = strripos($content, '</body>');
        $content = substr($content, 0, $pos) . $serializedContext . substr($content, $pos);

        $response->setContent($content);
    }
}