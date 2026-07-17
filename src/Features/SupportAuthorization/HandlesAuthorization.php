<?php

namespace Livewire\Features\SupportAuthorization;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use function Illuminate\Support\enum_value;

trait HandlesAuthorization
{
    use AuthorizesRequests;

    protected ?string $method = null;

    public function setAuthorizationMethod($method = null): void
    {
        $this->method = $method;
    }

    protected function parseAbilityAndArguments($ability, $arguments): array
    {
        $ability = enum_value($ability);

        if (is_string($ability) && ! str_contains($ability, '\\')) {
            // Need to reset the properties in case attribute is used along with `$this->authorize()`
            $this->setAuthorizationMethod();

            return [$ability, $arguments];
        }

        // Because this method override the original method,
        // we need to make sure it gets the right method name
        // if its called from `$this->authorize()` inside component action
        $method = $this->method ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];

        // Need to reset the properties in case attribute is used along with `$this->authorize()`
        $this->setAuthorizationMethod();

        return [$this->normalizeGuessedAbilityName($method), $ability];
    }
}