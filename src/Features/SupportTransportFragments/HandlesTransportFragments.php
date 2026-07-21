<?php

namespace Livewire\Features\SupportTransportFragments;

use LogicException;

trait HandlesTransportFragments
{
    protected array $transportFragmentStack = [];

    protected array $transportFragmentNames = [];

    public function startTransportFragment(mixed $name): string
    {
        $name = $this->validateTransportFragmentName($name);
        $metadata = null;
        $marker = '';

        if ($name !== null && ! isset($this->transportFragmentNames[$name])) {
            $metadata = [
                'type' => 'transport',
                'name' => $name,
                'token' => substr(hash('sha256', $name), 0, 16),
                'mode' => 'morph',
            ];

            $this->transportFragmentNames[$name] = true;
            $marker = $this->transportFragmentMarker('FRAGMENT', $metadata);
        }

        $this->transportFragmentStack[] = $metadata;

        return $marker;
    }

    public function endTransportFragment(): string
    {
        if ($this->transportFragmentStack === []) {
            return '';
        }

        $metadata = array_pop($this->transportFragmentStack);

        if ($metadata === null) return '';

        return $this->transportFragmentMarker('ENDFRAGMENT', $metadata);
    }

    public function resetTransportFragments(): void
    {
        $this->transportFragmentStack = [];
        $this->transportFragmentNames = [];
    }

    public function ensureTransportFragmentsAreClosed(): void
    {
        $openTransportFragments = array_values(array_filter(
            $this->transportFragmentStack,
            fn ($metadata) => is_array($metadata),
        ));

        if ($openTransportFragments === []) {
            $this->resetTransportFragments();

            return;
        }

        $names = implode(', ', array_column($openTransportFragments, 'name'));

        $this->resetTransportFragments();

        throw new LogicException("Unclosed transport fragment stack: [{$names}].");
    }

    protected function validateTransportFragmentName(mixed $name): ?string
    {
        if (! is_string($name)
            || strlen($name) > 256
            || ! preg_match('/\A[A-Za-z0-9][A-Za-z0-9_.:-]*\z/', $name)
        ) {
            return null;
        }

        return $name;
    }

    protected function transportFragmentMarker(string $type, array $metadata): string
    {
        $encodedMetadata = [];

        foreach ($metadata as $key => $value) {
            $encodedMetadata[] = "{$key}={$value}";
        }

        return '<!--[if '.$type.':'.implode('|', $encodedMetadata).']><![endif]-->';
    }
}
