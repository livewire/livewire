<?php

namespace Livewire\Mechanisms\HandleSynths;

use Livewire\Drawer\Utils;
use Livewire\Mechanisms\HandleComponents\ComponentContext;

class PersistedValueCodec
{
    const KEY = '__livewire_persisted';

    const VERSION = 1;

    public function __construct(protected HandleSynths $synths) {}

    public function encodeForStorage($value, $component, $path, $key)
    {
        if (! $this->requiresSynthesis($value)) return $value;

        $encoded = $this->synths->dehydrate(
            $value,
            new ComponentContext($component),
            $path,
        );

        return [
            static::KEY => [
                'version' => static::VERSION,
                'value' => $encoded,
                'signature' => $this->signatureFor($encoded, $key),
            ],
        ];
    }

    public function decodeFromStorage($value, $component, $path, $key)
    {
        if (! $this->isEnvelope($value)) return $value;

        $envelope = $value[static::KEY];

        if (! $this->isValidEnvelope($envelope, $key)) {
            throw new CorruptPersistedValueException;
        }

        try {
            return $this->synths->hydrate(
                $envelope['value'],
                new ComponentContext($component),
                $path,
            );
        } catch (\Throwable $e) {
            throw new CorruptPersistedValueException($e);
        }
    }

    protected function isEnvelope($value)
    {
        return is_array($value)
            && count($value) === 1
            && array_key_exists(static::KEY, $value);
    }

    protected function isValidEnvelope($envelope, $key)
    {
        if (! is_array($envelope) || count($envelope) !== 3) return false;

        if (($envelope['version'] ?? null) !== static::VERSION) return false;

        if (! array_key_exists('value', $envelope)) return false;

        if (! Utils::isSyntheticTuple($envelope['value'])) return false;

        if (! is_string($signature = $envelope['signature'] ?? null)) return false;

        return hash_equals(
            $this->signatureFor($envelope['value'], $key),
            $signature,
        );
    }

    protected function signatureFor($value, $key)
    {
        $payload = json_encode([
            'type' => 'livewire-persisted-value',
            'version' => static::VERSION,
            'key' => $key,
            'value' => $value,
        ], JSON_THROW_ON_ERROR);

        return hash_hmac('sha256', $payload, app('encrypter')->getKey());
    }

    protected function requiresSynthesis($value)
    {
        if (Utils::isAPrimitive($value)) return false;

        if (! is_array($value)) return true;

        foreach ($value as $child) {
            if ($this->requiresSynthesis($child)) return true;
        }

        return false;
    }
}
