<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use function Livewire\store;

#[\Attribute]
class BaseUrl extends LivewireAttribute
{
    public function __construct(
        public $as = null,
        public $history = false,
        public $keep = false,
    ) {}

    public function mount($params)
    {
        $useQueryString = $params['queryString'] ?? null;
        if ($useQueryString === null) {
            $queryStringAttribute = (new \ReflectionClass($this->component))->getAttributes(\Livewire\Attributes\QueryString::class)[0] ?? null;
            if ($queryStringAttribute) {
                $useQueryString = $queryStringAttribute->getArguments()[0] ?? true;
            }
        }

        if($useQueryString !== null && !$useQueryString) {
            store($this->component)->set('useQueryString', false);
            return;
        }

        $initialValue = request()->query($this->urlName(), 'noexist');

        if ($initialValue === 'noexist') return;

        $decoded = is_array($initialValue)
            ? json_decode(json_encode($initialValue), true)
            : json_decode($initialValue, true);

        $this->setValue($decoded === null ? $initialValue : $decoded);
    }

    public function dehydrate($context)
    {
        if (!$context->mounting || !store($this->component)->get('useQueryString', true)) return;

        $queryString = [
            'as' => $this->as,
            'use' => $this->history ? 'push' : 'replace',
            'alwaysShow' => $this->keep,
        ];

        $context->pushEffect('url', $queryString, $this->getName());
    }

    public function urlName()
    {
        return $this->as ?? $this->getName();
    }
}

