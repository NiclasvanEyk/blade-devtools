<?php

namespace NiclasvanEyk\BladeDevtools;

use function array_is_list;
use function is_int;

class ComponentDataSerializer
{
    private $depth = 0;

    public function __construct(
        private array $dontSerialize = [
            'Illuminate\\Foundation\\Application',
        ],
        private int $maxDepth = 5,
    ) {
    }

    public function serialize($value): array
    {
        $serialized = ['type' => 'aborted'];
        if ($this->depth > 2) {
            return $serialized;
        }

        if (is_array($value)) {
            return $this->serializeArray($value);
        } elseif (is_null($value)) {
            return ['type' => 'null', 'value' => null];
        } elseif (is_scalar($value)) {
            return ['type' => $this->serializeScalar($value), 'value' => $value];
        }

        return $this->serializeClass($value);

        // throw new Exception("I don't know how to serialize this: " . $value);
    }

    private function serializeArray(array $attributes): array
    {
        $serialized = [];

        foreach ($attributes as $name => $value) {
            $serialized[$name] = $this->serialize($value);
        }

        return [
            'type' => 'array',
            'list' => array_is_list($attributes),
            'value' => $serialized,
        ];
    }

    private function serializeScalar($scalar): string
    {
        if (is_string($scalar)) {
            return 'string';
        }
        if (is_int($scalar)) {
            return 'int';
        }
        if (is_float($scalar)) {
            return 'float';
        }
        if (is_null($scalar)) {
            return 'null';
        }
        if (is_bool($scalar)) {
            return 'bool';
        }

        throw new \Exception('Unknown scalar type!');
    }

    private function serializeClass(object $object): array
    {
        $this->depth += 1;

        $serialized = [];

        $reflected = new \ReflectionClass($object);
        $serialized['type'] = 'class';
        $serialized['class'] = $reflected->getName();

        if ($this->ignores($reflected)) {
            $serialized['ignored'] = true;
        }

        $properties = [];
        foreach ($reflected->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            try {
            $properties[$property->getName()] = [
                'name' => $property->getName(),
                'value' => $this->serialize($property->getValue($object)),
            ];
            } catch (\Throwable $exception) {

            }
        }
        $serialized['properties'] = $properties;

        $this->depth -= 1;

        return $serialized;
    }

    private function ignores(\ReflectionClass $class): bool
    {
        return in_array($class->getName(), $this->dontSerialize);
    }
}