<?php

namespace Livewire;

use Illuminate\Support\Str;

class DataCaster
{
    protected $casters;

    public function __construct()
    {
        $this->casters = $this->getCasters();
    }

    public function castTo($rawType, $value)
    {
        [$type, $extras] = $this->parseTypeAndExtras($rawType);

        $this->ensureTypeExists($type);

        return $this->runCasterCast($type, $extras, $value);
    }

    public function castFrom($rawType, $value)
    {
        [$type, $extras] = $this->parseTypeAndExtras($rawType);

        $this->ensureTypeExists($type);

        return $this->runCasterUncast($type, $extras, $value);
    }

    protected function ensureTypeExists($type)
    {
        $isSupported = isset($this->casters[$type]) || class_exists($type);

        throw_unless(
            $isSupported,
            new \Exception("Casting to type [{$type}] not supported.")
        );
    }

    protected function runCasterCast($type, $extras, $value)
    {
        if (isset($this->casters[$type])) {
            return $this->casters[$type]['cast']($value, $extras);
        }

        return (new $type)->cast($value);
    }

    protected function runCasterUncast($type, $extras, $value)
    {
        if (isset($this->casters[$type])) {
            return $this->casters[$type]['uncast']($value, $extras);
        }

        return (new $type)->uncast($value);
    }

    protected function getCasters()
    {
        return [
            'date' => [
                'cast' => function ($value, $extras) {
                    if (isset($extras['format'])) {
                        return \Carbon\Carbon::createFromFormat($extras['format'], $value);
                    }

                    return \Carbon\Carbon::parse($value);
                },
                'uncast' => function ($value, $extras) {
                    if (method_exists($value, 'format') && isset($extras['format'])) {
                        return $value->format($extras['format']);
                    }

                    if (method_exists($value, 'toString')) {
                        return $value->toString();
                    }

                    dump($value);
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

    protected function getCaster($type)
    {
        return $this->caster[$type];
    }

    protected function parseTypeAndExtras($rawType)
    {
        // If the user specified a date format.
        if (Str::startsWith($rawType, 'date:')) {
            $type = 'date';
            $extras = ['format' => Str::after($rawType, 'date:')];
        } else {
            $type = $rawType;
            $extras = [];
        }

        return [ $type, $extras ];
    }
}
