<?php

namespace NiclasvanEyk\BladeDevtools\Context;

use NiclasvanEyk\BladeDevtools\Paths\RemotePath;
use Ramsey\Uuid\Uuid;

/**
 * Holds the data that will later be consumed by the devtools UI.
 */
class ComponentContext
{
    public readonly string $id;

    public function __construct(
        public ?ComponentContext $parent = null,
        ?string $id = null,
        public array $data = [],
        public string $tag = "unknown",
        public string $view = "unknown",
        public string $class = "unknown",
        public ?RemotePath $file = null,
    ) {
        $this->id = $id ?? (string) Uuid::uuid4();
    }
}