<?php

namespace Tests\Browser\Pagination;

use Tests\Browser\Pagination\Post;
use Tests\Browser\Pagination\Message;
use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

class TailwindWithMultiplePagination extends BaseComponent
{
    use WithPagination;

    public $postsPage = 1;

    protected $paginatorNames = ['page', 'postsPage'];

    public function render()
    {
        return View::file(__DIR__.'/view-multiple-pagination.blade.php', [
            'posts' => Post::paginate(3, ['*'], 'postsPage'),
            'messages' => Message::paginate(3),
        ]);
    }
}
