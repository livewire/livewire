<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Features\SupportFormObjects\Form;

#[\Attribute]
class BaseUrl extends LivewireAttribute
{
    public function __construct(
        public $as = null,
        public $history = false,
        public $keep = false,
        public $except = null,
    ) {}

    public function mount()
    {
        if ($this->as === null && $this->isOnFormObjectProperty()) {
            $this->as = $this->getSubName();
        }

        $initialValue = request()->query($this->urlName(), 'noexist');

        if ($initialValue === 'noexist') return;

        $decoded = is_array($initialValue)
            ? json_decode(json_encode($initialValue), true)
            : json_decode($initialValue, true);

        $value = $decoded === null ? $initialValue : $decoded;

        $this->setValue($value);
    }

    public function dehydrate($context)
    {
        if (! $context->mounting) return;

        $queryString = [
            'as' => $this->as,
            'use' => $this->history ? 'push' : 'replace',
            'alwaysShow' => $this->keep,
            'except' => $this->except,
        ];

        $context->pushEffect('url', $queryString, $this->getName());
    }

    public function isOnFormObjectProperty()
    {
        $subTarget = $this->getSubTarget();

        return $subTarget && is_subclass_of($subTarget, Form::class);
    }

    public function urlName()
    {
        return $this->as ?? $this->getName();
    }
}

