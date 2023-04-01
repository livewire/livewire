<?php

namespace Livewire\Features\SupportPagination;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Livewire\ComponentHook;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportQueryString\SupportQueryString;
use Livewire\Features\SupportQueryString\Url;

use function Livewire\invade;
use function Livewire\on;
use function Livewire\wrap;

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

    function call($method, $params, $returnEarly)
    {
        $methods = [
            'previousPage',
            'nextPage',
            'gotoPage',
            'resetPage',
            'setPage',
        ];

        if (! in_array($method, $methods)) return;

        $returnEarly(
            wrap($this->component)->$method(...$params)
        );
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

        // As the page name key didn't exist when query string was initialised earlier,
        // we need to initalise it now so the page name gets added to the querystring.
        $this->addUrlHook($pageName, $queryStringDetails);
    }

    protected function getQueryStringDetails($pageName)
    {
        $pageNameQueryString = data_get($this->getQueryString(), 'paginators.' . $pageName);

        $pageNameQueryString['as'] ??= $pageName;
        $pageNameQueryString['use'] ??= 'push';
        $pageNameQueryString['alwaysShow'] ??= false;

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
        $use = $queryStringDetails['use'];
        $alwaysShow = $queryStringDetails['alwaysShow'];

        $this->setPropertyHook($key, new Url(as: $alias, use: $use, alwaysShow: $alwaysShow));
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
        $supportQueryStringHook = ComponentHookRegistry::getHook($this->component, SupportQueryString::class);

        return $supportQueryStringHook->getQueryString();
    }
}
