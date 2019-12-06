<?php

namespace Livewire;

class DataCaster
{
    protected $casters;

    public function __construct()
    {
        $this->casters = $this->getCasters();
    }

    public function castTo($type, $value)
    {
        $this->ensureTypeExists($type);

        return $this->runCasterHydrate($type, $value);
    }

    public function castFrom($type, $value)
    {
        $this->ensureTypeExists($type);

        return $this->runCasterDehydrate($type, $value);
    }

    public function ensureTypeExists($type)
    {
        $isSupported = isset($this->casters[$type]) || class_exists($type);

        throw_unless(
            $isSupported,
            new \Exception("Casting to type [{$type}] not supported.")
        );
    }

    public function runCasterHydrate($type, $value)
    {
        if (isset($this->casters[$type])) {
            return $this->casters[$type]['hydrate']($value);
        }

        return (new $type)->hydrate($value);
    }

    public function runCasterDehydrate($type, $value)
    {
        if (isset($this->casters[$type])) {
            return $this->casters[$type]['dehydrate']($value);
        }

        return (new $type)->dehydrate($value);
    }

    public function getCasters()
    {
        return [
            'date' => [
                'hydrate' => function ($value) {
                    return \Carbon\Carbon::parse($value);
                },
                'dehydrate' => function ($value) {
                    return $value->toString();
                },
            ],
            'collection' => [
                'hydrate' => function ($value) {
                    return collect($value);
                },
                'dehydrate' => function ($value) {
                    return $value->toArray();
                },
            ],
        ];
    }
}
