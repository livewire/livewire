<?php

namespace Livewire\Features\SupportPagination;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Livewire\ComponentHook;
use Livewire\ComponentHookRegistry;
use Livewire\Features\SupportQueryString\SupportQueryString;
use Livewire\Features\SupportQueryString\Url;
use Livewire\WithPagination;

use function Livewire\invade;
use function Livewire\on;

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

        /**
         * Store the default pagination views so they can be restored at the end of a Octane request.
         */
        $oldDefaultView = Paginator::$defaultView;
        $oldDefaultSimpleView = Paginator::$defaultSimpleView;

        on('flush-state', function() use ($oldDefaultView, $oldDefaultSimpleView) {
            Paginator::defaultView($oldDefaultView);
            Paginator::defaultSimpleView($oldDefaultSimpleView);
        });

        Paginator::defaultView($this->paginationView());
        Paginator::defaultSimpleView($this->paginationSimpleView());
    }

    protected function setPageResolvers()
    {
        CursorPaginator::currentCursorResolver(function ($pageName) {
            $paginators = invade($this->component)->paginators;
            
            if (! isset($paginators[$pageName])) {
                $queryStringDetails = $this->getQueryStringDetails($pageName);

                $paginators[$pageName] = $this->resolvePage($queryStringDetails['as'], '');

                invade($this->component)->paginators = $paginators;

                /**
                 * As the page name key didn't exist when query string was initialised earlier,
                 * we need to initalise it now so the page name gets added to the querystring.
                 */
                $this->addUrlHook($pageName, $queryStringDetails);
            }

            return Cursor::fromEncoded($paginators[$pageName]);
        });

        Paginator::currentPageResolver(function ($pageName) {
            $paginators = invade($this->component)->paginators;

            if (! isset($paginators[$pageName])) {
                $queryStringDetails = $this->getQueryStringDetails($pageName);

                $paginators[$pageName] = $this->resolvePage($queryStringDetails['as'], 1);

                invade($this->component)->paginators = $paginators;

                /**
                 * As the page name key didn't exist when query string was initialised earlier,
                 * we need to initalise it now so the page name gets added to the querystring.
                 */
                $this->addUrlHook($pageName, $queryStringDetails);
            }

            return (int) $paginators[$pageName];
        });
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
        $supportQueryString = new SupportQueryString;
        $supportQueryString->setComponent($this->component);

        return $supportQueryString->getQueryString();
    }
}
