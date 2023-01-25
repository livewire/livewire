<?php

namespace Livewire;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;

trait WithPagination
{
    public $paginators = [];

    protected $numberOfPaginatorsRendered = [];

    public function queryStringWithPagination()
    {
        return collect($this->paginators)->mapWithKeys(function ($page, $pageName) {
            return ['paginators.'.$pageName => ['use' => 'push', 'as' => $pageName]];
        });
    }

    public function initializeWithPagination()
    {
        if (class_exists(CursorPaginator::class)) {
            CursorPaginator::currentCursorResolver(function ($pageName){
                if (! isset($this->paginators[$pageName])) {
                    $this->paginators[$pageName] = request()->query($pageName, '');
                }

                return Cursor::fromEncoded($this->paginators[$pageName]);
            });
        }

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
        if (is_numeric($page)) {
            $page = (int) ($page <= 0 ? 1 : $page);
        }

        $beforePaginatorMethod = 'updatingPaginators';
        $afterPaginatorMethod = 'updatedPaginators';

        $beforeMethod = 'updating' . $pageName;
        $afterMethod = 'updated' . $pageName;

        if (method_exists($this, $beforePaginatorMethod)) {
            $this->{$beforePaginatorMethod}($page, $pageName);
        }

        if (method_exists($this, $beforeMethod)) {
            $this->{$beforeMethod}($page, null);
        }

        $this->paginators[$pageName] = $page;

        if (method_exists($this, $afterPaginatorMethod)) {
            $this->{$afterPaginatorMethod}($page, $pageName);
        }

        if (method_exists($this, $afterMethod)) {
            $this->{$afterMethod}($page, null);
        }
    }
}
