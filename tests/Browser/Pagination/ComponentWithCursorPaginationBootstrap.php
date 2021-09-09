<?php

namespace Tests\Browser\Pagination;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

class ComponentWithCursorPaginationBootstrap extends BaseComponent
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php', [
            'posts' => Post::cursorPaginate(3,'*','page')
        ]);
    }
}
