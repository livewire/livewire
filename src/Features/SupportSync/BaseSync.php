<?php

namespace Livewire\Features\SupportSync;

use Attribute;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use ReflectionNamedType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BaseSync extends LivewireAttribute
{
    protected const BUILTIN_STRATEGIES = [
        'int',
        'integer',
        'float',
        'double',
        'bool',
        'boolean',
        'string',
        'array',
        'object',
    ];

    public function __construct(
        protected ?string $using = null,
    ) {}

    public function toLivewire(mixed $value): mixed
    {
        $strategy = $this->resolveStrategy();

        if ($this->isBuiltinStrategy($strategy)) {
            return $this->castBuiltInToLivewire($strategy, $value);
        }

        return $this->resolveCodec($strategy)->toLivewire($value);
    }

    public function fromLivewire(mixed $value): mixed
    {
        $strategy = $this->resolveStrategy();

        if ($this->isBuiltinStrategy($strategy)) {
            return $this->castBuiltInFromLivewire($strategy, $value);
        }

        return $this->resolveCodec($strategy)->fromLivewire($value);
    }

    public function update($fullPath, $newValue)
    {
        if (! str_contains($fullPath, '.')) return;

        if ($this->isBuiltinStrategy($this->resolveStrategy())) return;

        throw new \InvalidArgumentException(
            "Synced property [{$this->getName()}] does not support deep updates. Update the property as a whole value."
        );
    }

    public function getClientStrategy(): string
    {
        return $this->resolveStrategy();
    }

    protected function resolveStrategy(): string
    {
        if ($this->using !== null) {
            return ltrim($this->using, '\\');
        }

        $reflection = new \ReflectionProperty($this->component, $this->getName());
        $type = $reflection->getType();

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();

            if (in_array(strtolower($name), self::BUILTIN_STRATEGIES, true)) {
                return $name;
            }
        }

        throw new \InvalidArgumentException(
            "Unable to infer sync strategy for [{$this->getName()}]. Provide one explicitly, for example: #[Sync('int')] or #[Sync(CustomCodec::class)]."
        );
    }

    protected function isBuiltinStrategy(string $strategy): bool
    {
        return in_array(strtolower($strategy), self::BUILTIN_STRATEGIES, true);
    }

    protected function castBuiltInToLivewire(string $strategy, mixed $value): mixed
    {
        return $this->castBuiltInFromLivewire($strategy, $value);
    }

    protected function castBuiltInFromLivewire(string $strategy, mixed $value): mixed
    {
        return match (strtolower($strategy)) {
            'int', 'integer' => $this->castInteger($value),
            'float', 'double' => $this->castFloat($value),
            'bool', 'boolean' => $this->castBool($value),
            'string' => $this->castString($value),
            'array' => $this->castArray($value),
            'object' => $this->castObject($value),
            default => $value,
        };
    }

    protected function castInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') return null;
        if (is_int($value)) return $value;
        if (is_bool($value)) return $value ? 1 : 0;
        if (is_float($value) && floor($value) === $value) return (int) $value;

        if (is_string($value) || is_numeric($value)) {
            if (filter_var((string) $value, FILTER_VALIDATE_INT) !== false) {
                return (int) $value;
            }
        }

        throw new \InvalidArgumentException("Cannot sync value as [int] for property [{$this->getName()}].");
    }

    protected function castFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') return null;
        if (is_float($value)) return $value;
        if (is_int($value)) return (float) $value;

        if (is_string($value) || is_numeric($value)) {
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        throw new \InvalidArgumentException("Cannot sync value as [float] for property [{$this->getName()}].");
    }

    protected function castBool(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        if (is_int($value) || is_float($value)) return (bool) $value;
        if ($value === null || $value === '') return false;

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) return true;
            if (in_array($normalized, ['0', 'false', 'off', 'no', ''], true)) return false;
        }

        throw new \InvalidArgumentException("Cannot sync value as [bool] for property [{$this->getName()}].");
    }

    protected function castString(mixed $value): ?string
    {
        if ($value === null) return null;
        if (is_string($value)) return $value;
        if (is_scalar($value)) return (string) $value;
        if (is_object($value) && method_exists($value, '__toString')) return (string) $value;

        throw new \InvalidArgumentException("Cannot sync value as [string] for property [{$this->getName()}].");
    }

    protected function castArray(mixed $value): array
    {
        if ($value === null || $value === '') return [];
        if (is_array($value)) return $value;

        if (is_object($value) && $value instanceof \stdClass) {
            return (array) $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        throw new \InvalidArgumentException("Cannot sync value as [array] for property [{$this->getName()}].");
    }

    protected function castObject(mixed $value): \stdClass
    {
        if ($value instanceof \stdClass) return $value;
        if ($value === null || $value === '') return new \stdClass;
        if (is_array($value)) return (object) $value;

        if (is_string($value)) {
            $decoded = json_decode($value);

            if (json_last_error() === JSON_ERROR_NONE && is_object($decoded)) {
                return $decoded;
            }
        }

        throw new \InvalidArgumentException("Cannot sync value as [object] for property [{$this->getName()}].");
    }

    protected function resolveCodec(string $strategy): SyncCodec
    {
        if (! class_exists($strategy)) {
            throw new \InvalidArgumentException("Sync codec [{$strategy}] was not found.");
        }

        if (! is_subclass_of($strategy, SyncCodec::class)) {
            throw new \InvalidArgumentException(
                sprintf('Sync codec [%s] must implement [%s].', $strategy, SyncCodec::class)
            );
        }

        $codec = app($strategy);

        if (! $codec instanceof SyncCodec) {
            throw new \InvalidArgumentException("Sync codec [{$strategy}] could not be resolved.");
        }

        return $codec;
    }
}
