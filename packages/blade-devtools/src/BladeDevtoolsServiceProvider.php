<?php

namespace NiclasvanEyk\BladeDevtools;

use Illuminate\View\ViewServiceProvider;

class BladeDevtoolsServiceProvider extends ViewServiceProvider
{
    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__.'/../config/blade-devtools.php', 'blade-devtools'
        );
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
        $enabled = $this->app['config']->get('blade-devtools.enabled', false);
        if (! $enabled) {
            return parent::createFactory($resolver, $finder, $events);
        }

        return new CustomViewFactory($resolver, $finder, $events);
    }

    private function handlePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/blade-devtools.php' => config_path('blade-devtools.php'),
        ], 'blade-devtools-config');
    }
}
