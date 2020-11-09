<?php

namespace Livewire;

use Illuminate\Support\Facades\Gate;

trait WithGateAuthorization
{
    public function allow(string $gate, ...$params): bool
    {
        return Gate::allows($gate, ...$params);
    }

    public function deny(string $gate, ...$params): bool
    {
        return Gate::denies($gate, ...$params);
    }

    public function allowAny(array $gates, ...$params): bool
    {
        return Gate::any($gates, ...$params);
    }

    public function denyAny(array $gates, ...$params): bool
    {
        return Gate::none($gates, ...$params);
    }

    public function allowForUser($user, string $gate, ...$params): bool
    {
        return Gate::forUser($user)->allows($gate, ...$params);
    }

    public function denyForUser($user, string $gate, ...$params): bool
    {
        return Gate::forUser($user)->denies($gate, ...$params);
    }
}
