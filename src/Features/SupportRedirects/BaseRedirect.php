<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

use function Livewire\store;

#[\Attribute(\Attribute::TARGET_METHOD)]
class BaseRedirect extends LivewireAttribute
{
    public function __construct(
        public ?string $to = null,
        public bool $navigate = false,
        public \BackedEnum|string|null $route = null,
        public string|array|null $action = null,
        public mixed $parameters = [],
        public bool $absolute = true,
        public bool $intended = false,
        public string $default = '/',
    ) {}

    public function call()
    {
        return function () {
            // Remove any existing redirect state before applying the attribute redirect.
            if ($this->storeHas('redirect')) {
                store($this->component)->unset('redirect');
                store($this->component)->unset('redirectUsingNavigate');
            }

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
