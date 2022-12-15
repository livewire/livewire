<?php

namespace Tests\Browser\Pagination;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

#[\AllowDynamicProperties]
class ComponentWithTwoPaginatorsTailwind extends BaseComponent
{
    use WithPagination;

    public $pageHookOutput = null;
    public $itemPageHookOutput = null;

    public function updatedPage($page)
    {
        $this->pageHookOutput = 'page-is-set-to-' . $page;
    }

    public function updatedItemPage($page)
    {
        $this->itemPageHookOutput = 'item-page-is-set-to-' . $page;
    }

    public function render()
    {
        return View::file(__DIR__.'/component-with-two-paginators.blade.php', [
            'posts' => Post::paginate(3),
            'items' => Item::paginate(3, ['*'], 'itemPage'),
        ]);
    }
}
