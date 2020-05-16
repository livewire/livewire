<?php

namespace Livewire\Contracts;

use Livewire\Component;
use Livewire\Token;

interface TokenStorage
{
    public function clearToken(string $token): bool;
    public function getIdForToken(string $token): ?string;
    public function setToken(Token $token, Component $component): Token;
}
