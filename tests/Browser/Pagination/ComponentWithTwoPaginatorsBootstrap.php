<?php

namespace Tests\Browser\Pagination;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

#[\AllowDynamicProperties]
class ComponentWithTwoPaginatorsBootstrap extends BaseComponent
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $pageHookOutput = null;
    public $itemPageHookOutput = null;

    public function render()
    {
        return View::file(__DIR__.'/component-with-two-paginators.blade.php', [
            'posts' => Post::paginate(3),
            'items' => Item::paginate(3, ['*'], 'itemPage'),
        ]);
    }
}
