<?php

namespace Livewire;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;

trait WithCursorPagination
{
    public $cursor = '';

    public function getQueryString()
    {
        $queryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;

        return array_merge(['cursor' => ['except' => '']], $queryString);
    }

    public function initializeWithCursorPagination()
    {
        $this->cursor = $this->resolvePage();

        CursorPaginator::currentCursorResolver(function () {
            return Cursor::fromEncoded($this->cursor);
        });
        Paginator::defaultSimpleView($this->cursorPaginationView());
    }

    public function cursorPaginationView()
    {
        return 'livewire::cursor-' . (property_exists($this, 'paginationTheme') ? $this->paginationTheme : 'tailwind');
    }

    public function previousPage($cursor)
    {
        $this->setPage($cursor);
    }

    public function nextPage($cursor)
    {
        $this->setPage($cursor);
    }

    public function gotoPage($cursor)
    {
        if ($cursor instanceof Cursor) {
            $this->setPage($cursor->encode());
        } else {
            $this->setPage($page);
        }
    }

    public function resetPage()
    {
        $this->setPage();
    }

    public function setPage($cursor = '')
    {
        $this->cursor = $cursor;
    }

    public function resolvePage()
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return request()->query('cursor', $this->cursor);
    }
}
