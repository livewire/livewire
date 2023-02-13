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
        'foo' => ['use' => 'push', 'alwaysShow' => false],
        'bar' => ['use' => 'push', 'alwaysShow' => false],
        'bob' => ['use' => 'push', 'alwaysShow' => true],
        'qux' => ['use' => 'push', 'alwaysShow' => true],
        'showNestedComponent' => ['use' => 'push', 'alwaysShow' => true],
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
