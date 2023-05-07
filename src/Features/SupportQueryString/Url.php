<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

#[\Attribute]
class Url extends LivewireAttribute
{
    public function __construct(
        public $as = null,
        public $use = 'replace',
        public $alwaysShow = false,
    ) {}

    public function mount()
    {
        $initialValue = request()->query($this->urlName(), 'noexist');

        if ($initialValue === 'noexist') return;

        $decoded = is_array($initialValue)
            ? json_decode(json_encode($initialValue), true)
            : json_decode($initialValue, true);

        $this->setValue($decoded === null ? $initialValue : $decoded);
    }

    public function dehydrate($context)
    {
        if (! $context->mounting) return;

        $queryString = [
            'as' => $this->as,
            'use' => $this->use,
            'alwaysShow' => $this->alwaysShow,
        ];

        $context->pushEffect('url', $queryString, $this->getName());
    }

    public function urlName()
    {
        return $this->as ?? $this->getName();
    }
}

