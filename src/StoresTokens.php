<?php

namespace Livewire;

trait StoresTokens
{
    public function getIdForToken(string $token): ?string
    {
        return session("token:$token");
    }

    public function setToken(Token $token, Component $component): Token
    {
        session()->put("token:{$token->value()}", $component->id);

        return $token;
    }

    public function clearToken(string $token): bool
    {
        return session()->remove("token:$token") !== null;
    }
}
