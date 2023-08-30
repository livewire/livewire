<?php

namespace Livewire\Features\SupportEncryptedProperties;

use Livewire\ComponentHook;

use function Livewire\store;

class SupportEncryptedProperties extends ComponentHook
{
    public function maybeEncrypt($meta, $context, $path)
    {
        return $this->shouldEncrypt($meta, $context, $path)
            ? ['s' => 'crypt:' . encrypt($meta)]
            : $meta;
    }

    public function maybeDecrypt($subject)
    {
        return $this->isEncrypted($subject)
            ? decrypt(str($subject[1]['s'])->after('crypt:'))
            : $subject;
    }

    protected function shouldEncrypt($meta, $context, $path)
    {
        $component = $context->component;

        $encryptedPropertyNames = store($component)->get('encryptedProperties', []);

        return in_array($path, $encryptedPropertyNames);
    }

    protected function isEncrypted($subject)
    {
        if (! isset($subject[1]['s'])) return;

        $key = $subject[1]['s'];

        return str_starts_with($key, 'crypt:');
    }
}
