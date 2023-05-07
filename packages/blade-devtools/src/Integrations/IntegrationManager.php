<?php

namespace NiclasvanEyk\BladeDevtools\Integrations;

use Illuminate\Contracts\Foundation\Application;
use NiclasvanEyk\BladeDevtools\Contracts\Integration;
use Psr\Log\LoggerInterface;

class IntegrationManager
{
    /**
     * @var Integration[]
     */
    private array $integrations = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Application $app,
        ...$integrations,
    ) {
        $this->integrations = $this->validate($integrations);
    }

    /**
     * @param mixed[]
     * @return Integration[]
     */
    private function validate(array $integrations): array
    {
        /** @var Integration[] */
        $validIntegrations = [];
        foreach ($integrations as $integration) {
            if (! $integration instanceof Integration) {
                $interface = Integration::class;
                $this->logger->warning("Blade Devtools does not implement '$interface'!", [
                    'integration' => $integration::class,
                ]);

                continue;
            }

            $validIntegrations[] = $integration;
        }

        return $validIntegrations;
    }

    public function activate(): void
    {
        foreach ($this->integrations as $integration) {
            if (! $integration->shouldBeActivated($this->app)) {
                continue;
            }

            $this->logger->debug('Activating Blade Devtools integration.', [
                'integration' => $integration,
            ]);
            $integration->activate($this->app);
        }
    }

    // TODO: Frontend support (e.g. highlight AlpineJS attributes)?
}
