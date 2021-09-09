<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $page = 1;

    public $paginators = [];

    protected $numberOfPaginatorsRendered = [];

    public function getQueryString()
    {
        foreach ($this->paginators as $key => $value) {
            $this->$key = $value;
        }

        $queryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;

        foreach ($this->paginators as $key => $value) {
            $queryString[$key] = ['except' => 1];
        }

        return $queryString;
    }

    public function initializeWithPagination()
    {
        foreach ($this->paginators as $key => $value) {
            $this->$key = $value;
        }

        $this->page = $this->resolvePage();

        $this->paginators['page'] = $this->page;

        Paginator::currentPageResolver(function ($pageName) {
            if (! isset($this->paginators[$pageName])) {
                $this->paginators[$pageName] = request()->query($pageName, 1);
            }

            return (int) $this->paginators[$pageName];
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
        $this->setPage(max($this->paginators[$pageName] - 1, 1), $pageName);
    }

    public function nextPage($pageName = 'page')
    {
        $this->setPage($this->paginators[$pageName] + 1, $pageName);
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
        $this->syncInput('paginators.' . $pageName, $page);

        $this->syncInput($pageName, $page);
    }

    public function resolvePage()
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return (int) request()->query('page', $this->page);
    }
}
