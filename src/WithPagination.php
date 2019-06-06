<?php

namespace Livewire;

use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $paginator = [
        'page' => 1,
    ];

    public function initializeWithPagination()
    {
        Paginator::currentPageResolver(function () {
            return $this->paginator['page'];
        });

        Paginator::defaultView('livewire::pagination-links');
    }

    public function previousPage()
    {
        $this->paginator['page'] = $this->paginator['page'] - 1;
    }

    public function nextPage()
    {
        $this->paginator['page'] = $this->paginator['page'] + 1;
    }

    public function gotoPage($page)
    {
        $this->paginator['page'] = $page;
    }
}
