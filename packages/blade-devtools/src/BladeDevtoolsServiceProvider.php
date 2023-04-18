<?php

namespace NiclasvanEyk\BladeDevtools;

use Illuminate\View\ViewServiceProvider;

class BladeDevtoolsServiceProvider extends ViewServiceProvider
{
    // TODO: Bail, if this package should not be enabled (e.g. if APP_ENV !== "local")

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
        return new CustomViewFactory($resolver, $finder, $events);
    }
}
