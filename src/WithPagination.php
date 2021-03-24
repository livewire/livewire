<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $pageName = 'page';

    public function getQueryString()
    {
        $queryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;

        return array_merge([$this->pageName => ['except' => 1]], $queryString);
    }

    public function initializeWithPagination()
    {
        $this->page = $this->resolvePage();

        Paginator::currentPageResolver(function () {
            return (int)$this->getPage();
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
        $this->setPage(max($this->getPage() - 1, 1));
    }

    public function nextPage()
    {
        $this->setPage($this->getPage() + 1);
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
        $this->{$this->pageName} = $page;
    }

    public function resolvePage()
    {
        // The "page name" query string item should only be available
        // from within the original component mount run.
        return (int)request()->query($this->pageName, $this->getPage());
    }

    public function getPage()
    {
        return object_get($this, $this->pageName, 1);
    }

    public function getPublicPropertiesDefinedBySubClass()
    {
        return tap(parent::getPublicPropertiesDefinedBySubClass(), function (&$props) {
            $props[$this->pageName] = $this->getPage();
        });
    }

}
