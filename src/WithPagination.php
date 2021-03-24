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

        $queryParameters = array_fill_keys($this->getPaginatorNames(), ['except' => 1]);

        return array_merge($queryParameters, $this->queryString);
    }

    public function initializeWithPagination()
    {
        foreach($this->getPaginatorNames() as $pageName) {
            $this->{$this->getPageName($pageName)} = $this->resolvePage($pageName);
        }

        Paginator::currentPageResolver(function ($pageName = 'page') {
            return $this->{$this->getPageName($pageName)};
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
        $this->setPage(max($this->{$this->getPageName($pageName)} - 1, 1), $pageName);
    }

    public function nextPage($pageName = 'page')
    {
        $this->setPage($this->{$this->getPageName($pageName)} + 1, $pageName);
    }

    public function gotoPage($page, $pageName = 'page')
    {
        $this->setPage($page, $pageName);
    }

    public function resetPage($pageName = 'page')
    {
        $this->setPage(1, $pageName);
    }

    public function setPage($page, $pageName = 'page')
    {
        $this->{$this->getPageName($pageName)} = $page;
    }

    public function resolvePage($pageName = 'page')
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return request()->query($this->getPageName($pageName), $this->{$this->getPageName($pageName)});
    }

    public function getPageName($pageName = 'page')
    {
        return $pageName;
    }

    public function getPaginatorNames()
    {
        return property_exists($this, 'paginatorNames') ? $this->paginatorNames : ['page'];
    }
}
