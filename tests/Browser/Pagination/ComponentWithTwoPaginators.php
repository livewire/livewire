<?php

namespace Tests\Browser\Pagination;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

class ComponentWithTwoPaginators extends BaseComponent
{
    use WithPagination;

    public function render()
    {
        return View::file(__DIR__.'/component-with-two-paginators.blade.php', [
            'posts' => Post::paginate(3),
            'items' => Item::paginate(3),
        ]);
    }
}
