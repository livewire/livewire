<?php

namespace Tests\Browser\Pagination;

use Tests\Browser\Pagination\Post;
use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

class PaginationComponentWithCustomQueryString extends BaseComponent
{
    use WithPagination;

    protected $queryString = [
        'page' => ['except' => 2]
    ];

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php', [
            'posts' => Post::paginate(3),
        ]);
    }
}
