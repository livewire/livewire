<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $pageNames = ['page'];

    public function getQueryString()
    {
        return array_merge(collect($this->pageNames)->map(function($i) {
            return [$i => ['except' => 1]];
        }), $this->queryString);
    }

    public function initializeWithPagination()
    {
        foreach ($this->pageNames as $pageName) {
            $this->$pageName = $this->resolvePage();
        }

        Paginator::currentPageResolver(function ($pageName = 'page') {
            return $this->$pageName;
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

    public function previousPage($pageName = 'page')
    {
        $this->setPage(max($this->$pageName - 1, 1));
    }

    public function nextPage($pageName = 'page')
    {
        $this->setPage($this->$pageName + 1);
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function resetPage()
    {
        $this->setPage(1);
    }

    public function setPage($page, $pageName = 'page')
    {
        $this->$pageName = $page;
    }

    public function resolvePage($pageName = 'page')
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return request()->query($pageName, $this->$pageName);
    }
}
