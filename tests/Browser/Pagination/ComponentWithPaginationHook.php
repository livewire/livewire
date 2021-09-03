<?php

namespace Tests\Browser\Pagination;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

class ComponentWithPaginationHook extends BaseComponent
{
    use WithPagination;

    public $hookOutput = null;

    public function updatedPage($page)
    {
        $this->hookOutput = 'page-is-set-to-' . $page;
    }

    public function render()
    {
        return View::file(__DIR__.'/component-with-pagination-hook.blade.php', [
            'posts' => Post::paginate(3),
        ]);
    }
}
