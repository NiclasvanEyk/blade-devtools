<?php

namespace NiclasvanEyk\BladeDevtools;

use Illuminate\Contracts\Http\Kernel as KernelInterface;
use Illuminate\View\ViewServiceProvider;
use NiclasvanEyk\BladeDevtools\Adapter\ViewFactoryDevtools;
use NiclasvanEyk\BladeDevtools\Context\RenderingContextManager;
use NiclasvanEyk\BladeDevtools\Contracts\Integration;
use NiclasvanEyk\BladeDevtools\Http\Middleware\InjectBladeDevtoolsRenderingData;
use NiclasvanEyk\BladeDevtools\Integrations\IntegrationManager;
use NiclasvanEyk\BladeDevtools\OpenInEditor\EditorLinkGenerator;
use NiclasvanEyk\BladeDevtools\OpenInEditor\EditorLinkGeneratorFactory;
use NiclasvanEyk\BladeDevtools\Overrides\CustomBladeCompiler;
use NiclasvanEyk\BladeDevtools\Overrides\CustomViewFactory;
use NiclasvanEyk\BladeDevtools\Paths\PathConverter;
use Psr\Log\LoggerInterface;

class BladeDevtoolsServiceProvider extends ViewServiceProvider
{
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/blade-devtools.php',
            'blade-devtools'
        );

        $this->whenEnabled(function () {
            $this->app->singleton(RenderingContextManager::class);
            $this->registerCustomBladeCompiler();
            $this->registerPathConverter();
            $this->registerIntegrations();
            $this->provideEditorLinks();
        });
    }

    public function boot(): void
    {
        $this->handlePublishing();

        $this->whenEnabled(function (
            LoggerInterface $logger,
            IntegrationManager $integrations,
        ) {
            $this->injectMiddleware($logger);
            $integrations->activate();
        });
    }

    private function whenEnabled(callable $task): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->app->call($task);
    }

    /**
     * Create a new Factory Instance.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        if (! $this->isEnabled()) {
            return parent::createFactory($resolver, $finder, $events);
        }

        $factory = new CustomViewFactory($resolver, $finder, $events);

        $devtools = $this->app->make(ViewFactoryDevtools::class);
        $factory->setDevtools($devtools);

        return $factory;
    }

    /**
     * Register the Blade compiler implementation.
     */
    public function registerCustomBladeCompiler(): void
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return tap(new CustomBladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            ), function ($blade) {
                $blade->component('dynamic-component', \Illuminate\View\DynamicComponent::class);
            });
        });
    }

    protected function isEnabled(): bool
    {
        return $this->app['config']->get('blade-devtools.enabled', false);
    }

    private function handlePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/blade-devtools.php' => $this->app->configPath('blade-devtools.php'),
        ], 'blade-devtools-config');
    }

    private function registerPathConverter(): void
    {
        $this->app->singleton(PathConverter::class);

        $this
            ->app
            ->when(PathConverter::class)
            ->needs('$localSitesPath')
            ->giveConfig('blade-devtools.local_sites_path');

        $this
            ->app
            ->when(PathConverter::class)
            ->needs('$remoteSitesPath')
            ->giveConfig('blade-devtools.remote_sites_path');
    }

    private function provideEditorLinks(): void
    {
        $this->app->singleton(EditorLinkGeneratorFactory::class);

        $this->app->bind(
            EditorLinkGenerator::class,
            function (\Illuminate\Contracts\Foundation\Application $app) {
                /** @var EditorLinkGeneratorFactory $factory */
                $factory = $app->make(EditorLinkGeneratorFactory::class);
                $editor = $app['config']->get('blade-devtools.editor');

                return $factory->build($editor);
            }
        );
    }

    private function injectMiddleware(LoggerInterface $logger): void
    {
        $httpKernel = $this->app[KernelInterface::class];
        $middleware = InjectBladeDevtoolsRenderingData::class;

        if (! $httpKernel instanceof \Illuminate\Foundation\Http\Kernel) {
            $logger->warning("Blade Devtools cannot inject its $middleware middleware! This is where the devtools get their data from, so you somehow need to register it yourself if you want to use Blade Devtools. Usually the HttpKernelInterface is bound to an instance of Illuminate\\Foundation\\Http\\Kernel, however with your application, this does not seem to be the case.");

            return;
        }

        $httpKernel->pushMiddleware($middleware);
    }

    private function registerIntegrations(): void
    {
        $this->app->singleton(IntegrationManager::class);
        $this->app
            ->when(IntegrationManager::class)
            ->needs('$integrations')
            ->giveTagged(Integration::class);

        $this->app->tag([Integrations\Livewire::class], Integration::class);
    }
}
