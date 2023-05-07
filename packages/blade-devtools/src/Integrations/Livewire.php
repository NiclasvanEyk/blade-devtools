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

        if ($this->isDevtoolsComment($almostLastLine) && $this->isLivewireComment($lastLine)) {
            $lines[count($lines) - 2] = $lastLine;
            $lines[count($lines) - 1] = $almostLastLine;

            $repaired = implode("\n", $lines);

            \Log::info('Repaired response: '.$repaired);

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
