<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $page = 1;

    public function getQueryString()
    {
        $queryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;

        return array_merge(['page' => ['except' => 1]], $queryString);
    }

    public function initializeWithPagination()
    {
        $this->page = $this->resolvePage();

        Paginator::currentPageResolver(function () {
            return $this->page;
        });

        Paginator::defaultView($this->paginationView());
        Paginator::defaultSimpleView($this->paginationSimpleView());
    }

    public function paginationView()
    {
        return 'livewire::' . (property_exists($this, 'paginationTheme') ? $this->paginationTheme : 'tailwind');
    }

    public function paginationSimpleView()
    {
        return 'livewire::simple-' . (property_exists($this, 'paginationTheme') ? $this->paginationTheme : 'tailwind');
    }

    public function previousPage()
    {
        $this->setPage(max($this->page - 1, 1));
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function resetPage()
    {
        $this->setPage(1);
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function resolvePage()
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return request()->query('page', $this->page);
    }
}
