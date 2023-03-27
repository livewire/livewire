<?php

namespace Livewire\Features\SupportPagination;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Livewire\ComponentHook;
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
                $paginators[$pageName] = $this->resolvePage($pageName, '');

                invade($this->component)->paginators = $paginators;

                /**
                 * As the page name key didn't exist when query string was initialised earlier,
                 * we need to initalise it now so the page name gets added to the querystring.
                 */
                $this->initialiseQueryStringForPageName($pageName);
            }

            return Cursor::fromEncoded($paginators[$pageName]);
        });

        Paginator::currentPageResolver(function ($pageName) {
            $paginators = invade($this->component)->paginators;

            if (! isset($paginators[$pageName])) {
                $paginators[$pageName] = $this->resolvePage($pageName, 1);

                invade($this->component)->paginators = $paginators;

                /**
                 * As the page name key didn't exist when query string was initialised earlier,
                 * we need to initalise it now so the page name gets added to the querystring.
                 */
                $this->initialiseQueryStringForPageName($pageName);
            }

            return (int) $paginators[$pageName];
        });
    }

    protected function resolvePage($pageName, $default)
    {
        $as = data_get($this->getQueryString(), 'paginators.' . $pageName . '.as', $pageName);

        return request()->query($as, $default);
    }

    protected function initialiseQueryStringForPageName($pageName)
    {
        $pageQueryString = collect($this->getQueryString())->get('paginators.' . $pageName);

        if (is_null($pageQueryString)) return;

        $key = 'paginators.' . $pageName;
        $alias = $pageQueryString['as'] ?? $pageName;
        $use = $pageQueryString['use'] ?? 'push';
        $alwaysShow = $pageQueryString['alwaysShow'] ?? false;

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
