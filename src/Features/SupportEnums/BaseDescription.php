<?php

namespace Livewire\Features\SupportEnums;

#[\Attribute]
class BaseDescription
{
    public string $description;

    public function __construct($description) {
        $this->description = $description;
    }
}
