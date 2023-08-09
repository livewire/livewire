<?php

namespace Livewire\Features\SupportPagination;

use function Livewire\invade;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Livewire\ComponentHook;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportQueryString\SupportQueryString;
use Livewire\Features\SupportQueryString\Url;

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

    protected $restoreOverriddenPaginationViews;

    function boot()
    {
        $this->setPageResolvers();

        $this->overrideDefaultPaginationViews();
    }

    function destroy()
    {
        ($this->restoreOverriddenPaginationViews)();
    }

    function overrideDefaultPaginationViews()
    {
        $oldDefaultView = Paginator::$defaultView;
        $oldDefaultSimpleView = Paginator::$defaultSimpleView;

        $this->restoreOverriddenPaginationViews = function () use ($oldDefaultView, $oldDefaultSimpleView) {
            Paginator::defaultView($oldDefaultView);
            Paginator::defaultSimpleView($oldDefaultSimpleView);
        };

        Paginator::defaultView($this->paginationView());
        Paginator::defaultSimpleView($this->paginationSimpleView());
    }

    protected function setPageResolvers()
    {
        CursorPaginator::currentCursorResolver(function ($pageName) {
            $this->ensurePaginatorIsInitialized($pageName, defaultPage: '');

            return Cursor::fromEncoded($this->component->paginators[$pageName]);
        });

        Paginator::currentPageResolver(function ($pageName) {
            $this->ensurePaginatorIsInitialized($pageName);

            return (int) $this->component->paginators[$pageName];
        });
    }

    protected function ensurePaginatorIsInitialized($pageName, $defaultPage = 1)
    {
        if (isset($this->component->paginators[$pageName])) return;

        $queryStringDetails = $this->getQueryStringDetails($pageName);

        $this->component->paginators[$pageName] = $this->resolvePage($queryStringDetails['as'], $defaultPage);

        $this->addUrlHook($pageName, $queryStringDetails);
    }

    protected function getQueryStringDetails($pageName)
    {
        $pageNameQueryString = data_get($this->getQueryString(), 'paginators.' . $pageName);

        $pageNameQueryString['as'] ??= $pageName;
        $pageNameQueryString['history'] ??= true;
        $pageNameQueryString['keep'] ??= false;

        return $pageNameQueryString;
    }

    protected function resolvePage($alias, $default)
    {
        return request()->query($alias, $default);
    }

    protected function addUrlHook($pageName, $queryStringDetails)
    {
        $key = 'paginators.' . $pageName;
        $alias = $queryStringDetails['as'];
        $history = $queryStringDetails['history'];
        $keep = $queryStringDetails['keep'];

        // @todo: make this work...
        $this->component->setPropertyAttribute($key, new Url(as: $alias, history: $history, keep: $keep));
    }

    protected function paginationView()
    {
        if (method_exists($this->component, 'paginationView')) {
            return $this->component->paginationView();
        }

        return 'livewire::' . (property_exists($this->component, 'paginationTheme') ? invade($this->component)->paginationTheme : config('livewire.pagination_theme', 'tailwind'));
    }

    protected function paginationSimpleView()
    {
        return 'livewire::simple-' . (property_exists($this->component, 'paginationTheme') ? invade($this->component)->paginationTheme : config('livewire.pagination_theme', 'tailwind'));
    }

    protected function getQueryString()
    {
        $supportQueryStringHook = ComponentHookRegistry::getHook($this->component, SupportQueryString::class);

        return $supportQueryStringHook->getQueryString();
    }
}
