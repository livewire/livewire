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
        'foo' => ['history' => true, 'keep' => false],
        'bar' => ['history' => true, 'keep' => false],
        'bob' => ['history' => true, 'keep' => true],
        'qux' => ['history' => true, 'keep' => true],
        'showNestedComponent' => ['history' => true, 'keep' => true],
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
