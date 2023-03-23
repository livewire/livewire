<?php

namespace Livewire\Features\SupportPagination;

use Livewire\ComponentHook;
use Livewire\Features\SupportQueryString\SupportQueryString;

class SupportPagination extends ComponentHook
{
    static function provide()
    {
        app('livewire')->provide(function () {
            $this->loadViewsFrom(__DIR__.'/views', 'livewire');

            $paths = [__DIR__.'/views' => resource_path('views/vendor/livewire')];

            $this->publishes($paths, 'livewire');
            $this->publishes($paths, 'livewire:pagination');
        });
    }

    function dehydrate($context)
    {
        if (! property_exists($this->component, 'paginators')) return;
        if (! method_exists($this->component, 'queryStringWithPagination')) return;

        $paginators = $this->component->paginators;
        $componentQueryString = $this->getQueryString();

        if (is_null($paginators) && empty($componentQueryString)) return;

        foreach ($paginators as $pageName => $page) {
            if (! $queryString = $componentQueryString['paginators.'.$pageName]) return;

            $context->pushEffect('url', $queryString, 'paginators.'.$pageName);
        }
    }

    function getQueryString()
    {
        $supportQueryString = app(SupportQueryString::class);
        $supportQueryString->setComponent($this->component);
        return $supportQueryString->getQueryString();
    }
}
