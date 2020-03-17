<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $page = 1;

    public function isQueryStringEnabled()
    {
        if (property_exists($this, 'paginationQueryStringEnabled')) {
            return $this->paginationQueryStringEnabled;
        }
        return true;
    }

    public function getUpdatesQueryString()
    {
        if ($this->isQueryStringEnabled()) {
            return array_merge([$this->paginationQueryString], $this->updatesQueryString);
        }
    }

    public function initializeWithPagination()
    {
        if (!property_exists($this, 'paginationQueryString')) {
            $this->paginationQueryString = 'page';
        }

        $this->{$this->paginationQueryString} = 1;

        // The query string item should only be available
        // from within the original component mount run.
        if ($this->isQueryStringEnabled()) {
            $this->{$this->paginationQueryString} = request()->query(
                $this->paginationQueryString,
                $this->{$this->paginationQueryString}
            );
        }

        Paginator::currentPageResolver(function () {
            return $this->{$this->paginationQueryString};
        });

        Paginator::defaultView($this->paginationView());
    }

    public function paginationView()
    {
        return 'livewire::pagination-links';
    }

    public function previousPage()
    {
        $this->{$this->paginationQueryString} = $this->{$this->paginationQueryString} - 1;
    }

    public function nextPage()
    {
        $this->{$this->paginationQueryString} = $this->{$this->paginationQueryString} + 1;
    }

    public function gotoPage($page)
    {
        $this->{$this->paginationQueryString} = $page;
    }
}
