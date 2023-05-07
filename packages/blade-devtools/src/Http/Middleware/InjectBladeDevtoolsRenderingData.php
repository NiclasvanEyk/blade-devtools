<?php

namespace NiclasvanEyk\BladeDevtools\Http\Middleware;

use Closure;
use NiclasvanEyk\BladeDevtools\Context\ContextSerializer;
use NiclasvanEyk\BladeDevtools\Context\RenderingContext;
use NiclasvanEyk\BladeDevtools\Context\RenderingContextManager;
use function strripos;
use function substr;
use Symfony\Component\HttpFoundation\Response;

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
        if (! $context) {
            return $response;
        }

        $this->inject($context, $response);

        return $response;
    }

    private function inject(RenderingContext $context, Response $response): void
    {
        if (! str_contains($response->headers->get('content-type'), 'text/html')) {
            return;
        }

        $content = $response->getContent();
        $serializedContext = $this->serializer->toScriptTag($context);
        $pos = strripos($content, '</body>');
        $content = substr($content, 0, $pos).$serializedContext.substr($content, $pos);

        $response->setContent($content);
    }
}
