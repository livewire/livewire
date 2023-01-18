<?php

namespace LegacyTests\Browser\QueryString;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $foo = 'bar';
    public $bar = 'baz';
    public $bob = ['foo', 'bar'];
    public $qux = [
        'hyphen' => 'quux-quuz',
        'comma' => 'quux,quuz',
        'ampersand' => 'quux&quuz',
        'space' => 'quux quuz',
        'array' => [
            'quux',
            'quuz'
        ],
    ];

    public $showNestedComponent = false;

    protected $queryString = [
        'foo' => ['use' => 'push'],
        'bar' => ['use' => 'push'],
        // 'bob' => ['use' => 'push'],
        // 'qux' => ['use' => 'push'],
        'showNestedComponent' => ['use' => 'push'],
    ];

    public function modifyBob()
    {
        $this->bob = ['foo', 'bar', 'baz'];
    }

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
