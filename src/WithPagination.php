<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $page = 1;

    public function getUpdatesQueryString()
    {
        return array_merge(['page' => ['except' => 1]], $this->updatesQueryString);
    }

    public function initializeWithPagination()
    {
        $this->page = $this->resolvePage();

        Paginator::currentPageResolver(function () {
            return (int) $this->page;
        });

        Paginator::defaultView($this->paginationView());
    }

    public function paginationView()
    {
        return 'livewire::pagination-links';
    }

    public function previousPage()
    {
        $this->page = $this->page - 1;
    }

    public function nextPage()
    {
        $this->page = $this->page + 1;
    }

    public function gotoPage($page)
    {
        $this->page = $page;
    }

    public function resetPage()
    {
        $this->page = 1;
    }

    public function resolvePage()
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return (int) request()->query('page', $this->page);
    }
}
