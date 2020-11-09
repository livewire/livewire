<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public function getPageNames() {return ['page'];}

    /* TODO is this called once for all paginations, or called per each pagination?
    if it's once for all, we should build an array like this instead of the first argument:
        [
            'page' => ['except' => 1],
            'otherPageName' => ['except' => 1],
            ...
        ]
    if it's celled per each we should pass $pageName = 'page' as argument
    and replace the first argument with this:
    [$pageName => ['except' => 1]]*/
    public function getFromQueryString()
    {
        return array_merge(['page' => ['except' => 1]], $this->queryString);
    }

    public function initializeWithPagination()
    {
        foreach ($this->getPageNames() as $pageName) {
            $this->$pageName = $this->resolvePage();
        }

        // not sure if this closure needs default argument value as well $pageName = 'page'?
        Paginator::currentPageResolver(function ($pageName) {
            return $this->$pageName;
        });

        Paginator::defaultView($this->paginationView());
    }

    public function paginationView()
    {
        return 'livewire::pagination-links';
    }

    public function previousPage($pageName = 'page')
    {
        $this->$pageName = $this->$pageName - 1;
    }

    public function nextPage($pageName = 'page')
    {
        $this->$pageName = $this->$pageName + 1;
    }

    public function gotoPage($page, $pageName = 'page')
    {
        $this->$pageName = $page;
    }

    public function resetPage($pageName = 'page')
    {
        $this->$pageName = 1;
    }

    public function resolvePage($pageName = 'page')
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return request()->query($pageName, $this->$pageName);
    }
}
