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

        return $this->runCasterCast($type, $value);
    }

    public function castFrom($type, $value)
    {
        $this->ensureTypeExists($type);

        return $this->runCasterUncast($type, $value);
    }

    public function ensureTypeExists($type)
    {
        $isSupported = isset($this->casters[$type]) || class_exists($type);

        throw_unless(
            $isSupported,
            new \Exception("Casting to type [{$type}] not supported.")
        );
    }

    public function runCasterCast($type, $value)
    {
        if (isset($this->casters[$type])) {
            return $this->casters[$type]['cast']($value);
        }

        return (new $type)->cast($value);
    }

    public function runCasterUncast($type, $value)
    {
        if (isset($this->casters[$type])) {
            return $this->casters[$type]['uncast']($value);
        }

        return (new $type)->uncast($value);
    }

    public function getCasters()
    {
        return [
            'date' => [
                'cast' => function ($value) {
                    return \Carbon\Carbon::parse($value);
                },
                'uncast' => function ($value) {
                    if (method_exists($value, 'toString')) {
                        return $value->toString();
                    }

                    return $value->__toString();
                },
            ],
            'collection' => [
                'cast' => function ($value) {
                    return collect($value);
                },
                'uncast' => function ($value) {
                    return $value->toArray();
                },
            ],
        ];
    }
}
