<?php

namespace Livewire\V4\Paginators;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;

trait HandlesPaginators
{
    // Prefixing with __ to avoid conflicts with the WithPagination trait...
    protected $__paginatorInstances = [];

    public function setPaginatorInstance($paginator)
    {
        $this->__paginatorInstances[$paginator->getPageName()] = $paginator;
    }

    public function getPaginatorPayloads()
    {
        $payloads = [];

        foreach ($this->__paginatorInstances as $name => $paginator) {
            $payloads[$name] = $this->paginatorToPayload($paginator);
        }

        return $payloads;
    }

    protected function paginatorToPayload($paginator)
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'hasNextPage' => $paginator->hasMorePages(),
            'hasPreviousPage' => ! $paginator->onFirstPage(),
        ];
    }
}