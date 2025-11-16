
* Ensure compatibility with route caching

<?php

use Livewire\Livewire;
use Livewire\Configuration\Routes;
use Livewire\Configuration\Components;

return Livewire::configure()
    ->layout('layouts::app')
    ->withComponents(function (Components $components) {
        $components->directory(__DIR__.'/../app/Livewire');

        $components->namespace('pages', __DIR__.'/../resources/views/pages');
    })
    ->withRoutes(function (Routes $routes) {
        $routes->update(function ($handle) {
            return Route::get('/livewire/update', $handle)
                ->middleware('web');
        });

        $routes->upload(function ($handle) {
            return Route::post('/livewire/upload', $handle)
                ->middleware('web');
        });

        $routes->preview(function ($handle) {
            return Route::get('/livewire/preview/{filename}', $handle)
                ->middleware('web');
        });
    })->create();
