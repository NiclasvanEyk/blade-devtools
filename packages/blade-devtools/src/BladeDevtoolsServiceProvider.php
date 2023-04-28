<?php

namespace NiclasvanEyk\BladeDevtools;

use Illuminate\View\ViewServiceProvider;
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

        $this->registerCustomBladeCompiler();
    }

    public function boot(): void
    {
        $this->handlePublishing();
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