<?php

namespace Tests\Browser\Pagination;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

class ComponentWithCursorPaginationTailwind extends BaseComponent
{
    use WithPagination;

    public function render()
    {
        return View::file(__DIR__.'/component-with-pagination-hook.blade.php', [
            'posts' => Post::cursorPaginate(3,'*','page')
        ]);
    }
}
