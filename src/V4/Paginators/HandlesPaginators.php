<?php

namespace Livewire\V4\Paginators;

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

trait HandlesPaginators
{
    // Prefixing with __ to avoid conflicts with the WithPagination trait...
    protected $__paginatorInstances = [];

    public function setPaginatorInstance($paginator)
    {
        $name = $paginator instanceof CursorPaginator ? $paginator->getCursorName() : $paginator->getPageName();

        $this->__paginatorInstances[$name] = $paginator;
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
        if ($paginator instanceof CursorPaginator) {
            return [
                'type' => 'cursor',
                'count' => $paginator->count(),
                'hasMorePages' => $paginator->hasMorePages(),
                'perPage' => $paginator->perPage(),
                'hasPages' => $paginator->hasPages(),
                'onFirstPage' => $paginator->onFirstPage(),
                'onLastPage' => $paginator->onLastPage(),
                'cursorName' => $paginator->getCursorName(),
                'currentCursor' => $paginator->cursor()?->encode(),
                'nextCursor' => $paginator->nextCursor()?->encode(),
                'previousCursor' => $paginator->previousCursor()?->encode(),
            ];
        }

        $default = [
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'perPage' => $paginator->perPage(),
            'hasPages' => $paginator->hasPages(),
            'count' => $paginator->count(),
            'onFirstPage' => $paginator->onFirstPage(),
            'onLastPage' => $paginator->onLastPage(),
            'currentPage' => $paginator->currentPage(),
            'pageName' => $paginator->getPageName(),
        ];

        if ($paginator instanceof LengthAwarePaginator) {
            return array_merge($default, [
                'type' => 'lengthAware',
                'total' => $paginator->total(),
                'hasMorePages' => $paginator->hasMorePages(),
                'firstPage' => 1,
                'lastPage' => $paginator->lastPage(),
            ]);
        } elseif ($paginator instanceof Paginator) {
            return array_merge($default, [
                'type' => 'simple',
            ]);
        }

        throw new \Exception('Unsupported paginator type: ' . get_class($paginator));
    }
}
