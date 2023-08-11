<?php

namespace Livewire\Features\SupportPagination;

trait HandlesPagination
{
    protected $pageName = 'page';
    public $paginators = [];

    public function queryStringHandlesPagination()
    {
        return collect($this->paginators)->mapWithKeys(function ($page, $pageName) {
            return ['paginators.'.$pageName => ['history' => true, 'as' => $pageName, 'keep' => false]];
        })->toArray();
    }

    public function getPage($pageName = null)
    {
        return $this->paginators[($pageName ?: $this->pageName)] ?? 1;
    }

    public function previousPage($pageName = null)
    {
        $this->setPage(max(($this->paginators[($pageName ?: $this->pageName)] ?? 1) - 1, 1), ($pageName ?: $this->pageName));
    }

    public function nextPage($pageName = null)
    {
        $this->setPage(($this->paginators[($pageName ?: $this->pageName)] ?? 1) + 1, ($pageName ?: $this->pageName));
    }

    public function gotoPage($page, $pageName = null)
    {
        $this->setPage($page, ($pageName ?: $this->pageName));
    }

    public function resetPage($pageName = null)
    {
        $this->setPage(1, ($pageName ?: $this->pageName));
    }

    public function setPage($page, $pageName = null)
    {
        if (is_numeric($page)) {
            $page = (int) ($page <= 0 ? 1 : $page);
        }

        $beforePaginatorMethod = 'updatingPaginators';
        $afterPaginatorMethod = 'updatedPaginators';

        $beforeMethod = 'updating' . ($pageName ?: $this->pageName);
        $afterMethod = 'updated' . ($pageName ?: $this->pageName);

        if (method_exists($this, $beforePaginatorMethod)) {
            $this->{$beforePaginatorMethod}($page, ($pageName ?: $this->pageName));
        }

        if (method_exists($this, $beforeMethod)) {
            $this->{$beforeMethod}($page, null);
        }

        $this->paginators[($pageName ?: $this->pageName)] = $page;

        if (method_exists($this, $afterPaginatorMethod)) {
            $this->{$afterPaginatorMethod}($page, ($pageName ?: $this->pageName));
        }

        if (method_exists($this, $afterMethod)) {
            $this->{$afterMethod}($page, null);
        }
    }
}
