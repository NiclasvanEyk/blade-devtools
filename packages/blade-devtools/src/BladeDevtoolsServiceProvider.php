<?php

namespace NiclasvanEyk\BladeDevtools;

use Illuminate\Contracts\Http\Kernel as KernelInterface;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\View\ViewServiceProvider;
use NiclasvanEyk\BladeDevtools\Context\RenderingContextManager;
use NiclasvanEyk\BladeDevtools\Http\Middleware\InjectBladeDevtoolsRenderingData;
use NiclasvanEyk\BladeDevtools\Overrides\CustomBladeCompiler;
use NiclasvanEyk\BladeDevtools\Overrides\CustomViewFactory;

class BladeDevtoolsServiceProvider extends ViewServiceProvider
{
    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/blade-devtools.php',
            'blade-devtools'
        );

        if (!$this->isEnabled()) {
            return;
        }

        $this->app->singleton(RenderingContextManager::class);
        $this->registerCustomBladeCompiler();
    }

    public function boot(): void
    {
        $this->handlePublishing();

        if (!$this->isEnabled()) {
            return;
        }

        /** @var Kernel $httpKernel */
        $httpKernel = $this->app[KernelInterface::class];

        $httpKernel->pushMiddleware(InjectBladeDevtoolsRenderingData::class);
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
        if (!$this->isEnabled()) {
            return parent::createFactory($resolver, $finder, $events);
        }

        return new CustomViewFactory($resolver, $finder, $events);
    }

    /**
     * Register the Blade compiler implementation.
     *
     * @return void
     */
    public function registerCustomBladeCompiler()
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return tap(new CustomBladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            ), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
    }

    protected function isEnabled(): bool
    {
        return $this->app['config']->get('blade-devtools.enabled', false);
    }

    private function handlePublishing(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/blade-devtools.php' => config_path('blade-devtools.php'),
        ], 'blade-devtools-config');
    }
}