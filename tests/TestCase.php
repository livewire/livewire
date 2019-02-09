<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Livewire\LivewireServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Livewire\Facade;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [__DIR__.'/views']);
    }
}
