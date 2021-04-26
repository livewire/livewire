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
            return (int) $this->page;
        });

        Paginator::defaultView($this->paginationView());
        Paginator::defaultSimpleView($this->paginationSimpleView());
    }

    public function paginationView()
    {
        return 'livewire::' . $this->getPaginationTheme();
    }

    public function paginationSimpleView()
    {
        return 'livewire::simple-' . $this->getPaginationTheme();
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
        return (int) request()->query('page', $this->page);
    }

    public function getPaginationTheme(){
        return property_exists($this, 'paginationTheme')
            ? $this->paginationTheme
            : config('livewire.pagination_theme', 'tailwind');
    }
}
