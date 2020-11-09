<?php

namespace Tests\Browser\Nesting;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    protected $queryString = ['showChild'];

    public $showChild = false;
    public $key = 'foo';
    public $search;

    public function render()
    {
        $items = collect([
            'a' => 'Item #1',
            'b' => 'Item #2'
        ]);

        if ($this->search) {
            $items = $items->filter(function ($item) {
                return stripos($item, $this->search) !== false;
            });
        }

        return View::file(__DIR__ . '/view.blade.php', [
            'items' => $items
        ]);
    }
}
