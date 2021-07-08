<?php

namespace Livewire;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;

trait WithCursorPagination
{
    public $cursor = '';
    protected $paginations = [
        'cursor'
    ];

    public function getQueryString()
    {
        $queryString = method_exists($this, 'queryString')
            ? $this->queryString()
            : $this->queryString;
        $paginationsQueryStrings = array_fill_keys($this->paginations, ['except' => '']);
        return array_merge($paginationsQueryStrings, $queryString);
    }

    public function initializeWithCursorPagination()
    {

        array_walk($this->paginations, function ($value, $key) {
            if (!property_exists($this, $key))
                return;
            $this->{$key} = $this->resolvePage($key);
        });

        CursorPaginator::currentCursorResolver(function ($name) {
            return Cursor::fromEncoded($this->{$name} ?? 'cursor');
        });
        Paginator::defaultSimpleView($this->cursorPaginationView());
    }

    public function cursorPaginationView()
    {
        return 'livewire::cursor-' . (property_exists($this, 'paginationTheme') ? $this->paginationTheme : 'tailwind');
    }

    public function previousPage($cursor, $name = 'cursor')
    {
        $this->setPage($cursor, $name);
    }

    public function nextPage($cursor, $name = 'cursor')
    {
        $this->setPage($cursor, $name);
    }

    public function gotoPage($cursor, $name = 'cursor')
    {
        if ($cursor instanceof Cursor) {
            $this->setPage($cursor->encode(), $name);
        } else {
            $this->setPage($page, $name);
        }
    }

    public function resetPage()
    {
        $this->setPage('', $name);
    }

    public function setPage($cursor = '', $name = 'cursor')
    {
        if (!property_exists($this, $name))
            return;
        $this->{$name} = $cursor;
    }

    public function resolvePage($key)
    {
        // The "page" query string item should only be available
        // from within the original component mount run.
        return request()->query($key, $this->{$key});
    }
}
