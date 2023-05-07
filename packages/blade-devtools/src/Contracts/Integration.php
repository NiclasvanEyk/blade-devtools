<?php

namespace NiclasvanEyk\BladeDevtools\Contracts;

use Illuminate\Contracts\Foundation\Application;

/**
 * Optional functionaliy for supporting external libraries like Livewire.
 */
interface Integration
{
    /**
     * Whether this integration should be activated for the given application.
     */
    public function shouldBeActivated(Application $application): bool;

    /**
     * Register bindings into the container or boot your services.
     *
     * This function will be called after booting the
     * {@link \NiclasvanEyk\BladeDevtools\BladeDevtoolsServiceProvider}
     */
    public function activate(Application $application): void;
}
