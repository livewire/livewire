<?php

namespace Livewire\Features\SupportEncryptedProperties;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[\Attribute]
class BaseEncrypted extends LivewireAttribute
{
    public function dehydrate($context)
    {
        store($this->component)->push('encryptedProperties', $this->getName());
    }
}
