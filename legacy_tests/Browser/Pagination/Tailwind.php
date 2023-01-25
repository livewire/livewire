<?php

namespace LegacyTests\Browser\Pagination;

use Livewire\Component as BaseComponent;
use LegacyTests\Browser\Pagination\Post;
use Illuminate\Support\Facades\View;
use Livewire\WithPagination;

class Tailwind extends BaseComponent
{
    use WithPagination;

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php', [
            'posts' => Post::paginate(3),
        ]);
    }
}
