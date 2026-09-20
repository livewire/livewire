<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class BaseRedirect extends LivewireAttribute
{
    public function __construct(
        public ?string $to = null,
        public bool $navigate = false,
        public ?string $route = null,
        public string|array|null $action = null,
        public array $parameters = [],
        public bool $absolute = true,
        public bool $intended = false,
        public string $default = '/',
    ) {}

    public function call()
    {
        return function () {
            if ($this->intended) {
                $this->component->redirectIntended($this->default, $this->navigate);
                return;
            }

            if ($this->route !== null) {
                $this->component->redirectRoute($this->route, $this->parameters, $this->absolute, $this->navigate);
                return;
            }

            if ($this->action !== null) {
                $this->component->redirectAction($this->action, $this->parameters, $this->absolute, $this->navigate);
                return;
            }

            if ($this->to !== null) {
                $this->component->redirect($this->to, $this->navigate);
            }
        };
    }
}
