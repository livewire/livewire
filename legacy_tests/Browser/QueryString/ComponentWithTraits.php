<?php

namespace LegacyTests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithPagination;

class ComponentWithTraits extends Component
{
    use WithPagination;
    use WithSearch;

    public function render()
    {
        return View::file(__DIR__.'/component-with-traits.blade.php', [
            'posts' => Post::query()
                ->when($this->search, function ($query, $search) {
                    $query->where('title', 'LIKE', "%$search%");
                })
                ->paginate(3),
        ]);
    }
}
