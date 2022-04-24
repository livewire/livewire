<?php

namespace Tests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\WithPagination;
use Tests\Browser\Pagination\Post;

class ComponentWithDotNotation extends Component
{
    use WithPagination;
    use WithSearch;

    public $filters = [];

    protected $queryString = [
        'page' => ['except' => 1, 'as' => 'p'],
        'filters.title' => ['except' => '', 'as' => 'title']
    ];

    public function changePostTitle()
    {
        $this->filters = [
            'title' => 'Post #2'
        ];
    }

    public function mount()
    {
        $this->filters = [
            'title' => 'Post'
        ];
    }

    public function render()
    {
        return View::file(__DIR__.'/dot-notation.blade.php', [
            'posts' => Post::query()
                ->when($this->filters['title'], function ($query, $filter) {
                    $query->where('title', 'LIKE', "%$filter%");
                })
                ->paginate(3),
        ]);
    }
}
