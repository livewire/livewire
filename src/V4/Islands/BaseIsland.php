<?php

namespace Livewire\V4\Islands;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class BaseIsland extends LivewireAttribute
{
    public function __construct(
        public string $name,
        public ?string $view = null,
        public array $data = [],
        public string $mode = 'replace',
    ) {}

    public function call()
    {
        $this->component->island($this->name, $this->view, $this->data, $this->mode);
    }
}