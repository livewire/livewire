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

        return array_merge([$this->getPageName() => ['except' => 1]], $this->queryString);
    }

    public function initializeWithPagination()
    {
        $this->page = $this->resolvePage();

        Paginator::currentPageResolver(function () {
            return $this->{$this->getPageName()};
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
        $this->setPage(max($this->{$this->getPageName()} - 1, 1));
    }

    public function nextPage()
    {
        $this->setPage($this->{$this->getPageName()} + 1);
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
        $this->{$this->getPageName()} = $page;
    }

    public function resolvePage()
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return request()->query($this->getPageName(), $this->page);
    }
    
    public function getPageName()
    {
        return 'page';
    }
}
