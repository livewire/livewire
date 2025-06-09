<?php

namespace Livewire\V4\Page;

use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    public function test_livewire_route_helper()
    {
        Livewire::namespace('pages', __DIR__ . '/fixtures/pages');

        app('view')->addNamespace('layouts', __DIR__ . '/fixtures/layouts');

        Livewire::route('/dashboard', 'pages::dashboard');

        $this
            ->withoutExceptionHandling()
            ->get('/dashboard')
            ->assertSee('Layout [bar]')
            ->assertSee('Dashboard');
    }
}
