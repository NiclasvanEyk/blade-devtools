<?php

namespace NiclasvanEyk\BladeDevtools\Integrations;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;
use Livewire\LivewireManager;
use NiclasvanEyk\BladeDevtools\Contracts\Integration;
use Psr\Log\LoggerInterface;

class Livewire implements Integration
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function shouldBeActivated(Application $application): bool
    {
        return class_exists(LivewireManager::class);
    }

    public function activate(Application $application): void
    {
        $application->call(function (LivewireManager $livewire) {
            $livewire->listen(
                'component.dehydrate.initial',
                Closure::fromCallable([$this, 'onComponentDehydrate'])
            );
        });
    }

    public function onComponentDehydrate($component, $response): void
    {
        if (! $html = data_get($response, 'effects.html')) {
            return;
        }

        $lines = explode("\n", $html);
        if (count($lines) < 2) {
            return;
        }

        $almostLastLine = $lines[count($lines) - 2];
        $lastLine = $lines[count($lines) - 1];

        // Livewire adds an "end comment" to identify if you violated it's
        // restriction of having a _single_ root node per component:
        //     <div wire:id="my-id"></div>
        //     <-- Livewire component end my-id -->
        // When there is anything between the node with wire-id and it's end
        // comment, Livewire logs a warning to the console, since re-rendering
        // is likely to lead to errors.
        //
        // Since Blade Devtools adds comments arround views, this is violated
        // by default:
        //     <div wire:id="1234567"></div>
        // becomes
        //     <-- BLADE_COMPONENT_START[abc-xyz] -->
        //     <div wire:id="1234567"></div>
        //     <-- BLADE_COMPONENT_END[abc-xyz] -->
        // and after Livewires tracking logic injects the end comment:
        //     <-- BLADE_COMPONENT_START[abc-xyz] -->
        //     <div wire:id="1234567"></div>
        //     <-- BLADE_COMPONENT_END[abc-xyz] -->
        //     <-- Livewire component end 1234567 -->
        // As you can see, the BLADE_COMPONENT_END comment violates Livewire's
        // restriction. To resolve this issue, we simply swap the last two
        // lines, since the devtools do not care about comments anyway.
        if ($this->isDevtoolsComment($almostLastLine) && $this->isLivewireComment($lastLine)) {
            $lines[count($lines) - 2] = $lastLine;
            $lines[count($lines) - 1] = $almostLastLine;

            $repaired = implode("\n", $lines);

            data_set($response, 'effects.html', $repaired);
        }
    }

    private function isDevtoolsComment(string $line): bool
    {
        return Str::startsWith(trim($line), '<!-- BLADE_COMPONENT_');
    }

    private function isLivewireComment(string $line): bool
    {
        return Str::startsWith(trim($line), '<!-- Livewire');
    }
}
