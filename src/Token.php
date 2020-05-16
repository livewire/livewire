<?php

namespace Livewire;

use Livewire\Contracts\ResolvesToken;
use Livewire\Contracts\TokenStorage;

class Token
{
    /**
     * @var string $value
     */
    protected $value;

    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var TokenStorage
     */
    public $storage;

    public function __construct(TokenStorage $storage)
    {
        $this->storage = $storage;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->id
            ? "token#{$this->value}#{$this->id}"
            : "token#{$this->value}";
    }

    /**
     * Get or set a token from a string
     */
    public static function for(string $value, Component $component = null): self
    {
        $token = resolve(self::class);
        $token->value = $value;

        if ($component) {
            $token->storage->setToken($token, $component);
        } else {
            $token->id = $token->storage->getIdForToken($value);
        }

        return $token;
    }
}

