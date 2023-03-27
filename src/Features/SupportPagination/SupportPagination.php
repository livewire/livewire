<?php

namespace Livewire\Features\SupportPagination;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Livewire\ComponentHook;
use Livewire\Features\SupportQueryString\SupportQueryString;
use Livewire\WithPagination;

use function Livewire\invade;

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

    function boot()
    {
        if (! in_array(WithPagination::class, class_uses_recursive($this->component))) return;

        $this->setPageResolvers();

        Paginator::defaultView($this->paginationView());
        Paginator::defaultSimpleView($this->paginationSimpleView());
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

    protected function setPageResolvers()
    {
        CursorPaginator::currentCursorResolver(function ($pageName) {
            $paginators = invade($this->component)->paginators;
            
            if (! isset($paginators[$pageName])) {
                $paginators[$pageName] = $this->resolvePage($pageName, '');

                invade($this->component)->paginators = $paginators;
            }

            return Cursor::fromEncoded($paginators[$pageName]);
        });

        Paginator::currentPageResolver(function ($pageName) {
            $paginators = invade($this->component)->paginators;

            if (! isset($paginators[$pageName])) {
                $paginators[$pageName] = $this->resolvePage($pageName, 1);

                invade($this->component)->paginators = $paginators;
            }

            return (int) $paginators[$pageName];
        });
    }

    protected function resolvePage($pageName, $default)
    {
        $as = data_get($this->getQueryString($this->component), 'paginators.' . $pageName . '.as', $pageName);

        return request()->query($as, $default);
    }

    protected function paginationView()
    {
        return 'livewire::' . (property_exists($this->component, 'paginationTheme') ? invade($this->component)->paginationTheme : 'tailwind');
    }

    protected function paginationSimpleView()
    {
        return 'livewire::simple-' . (property_exists($this->component, 'paginationTheme') ? invade($this->component)->paginationTheme : 'tailwind');
    }

    protected function getQueryString()
    {
        $supportQueryString = new SupportQueryString;
        $supportQueryString->setComponent($this->component);

        return $supportQueryString->getQueryString();
    }
}
