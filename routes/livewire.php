<?php

use Illuminate\Support\Facades\Route;
use Livewire\Controllers\FilePreviewHandler;
use Livewire\Controllers\FileUploadHandler;
use Livewire\Controllers\HttpConnectionHandler;
use Livewire\Controllers\LivewireJavaScriptAssets;

Route::group([
    'namespace' => config('livewire.routes_namespace', null),
    'domain' => config('livewire.routes_domain', null),
    'prefix' => config('livewire.routes_prefix', null),
], function () {
    Route::middleware(config('livewire.middleware_group', ''))->group(function () {
        Route::post('/livewire/message/{name}', HttpConnectionHandler::class)
            ->name('livewire.message');

        Route::post('/livewire/upload-file', [FileUploadHandler::class, 'handle'])
            ->name('livewire.upload-file');

        Route::get('/livewire/preview-file/{filename}', [FilePreviewHandler::class, 'handle'])
            ->name('livewire.preview-file');
    });

    Route::get('/livewire/livewire.js', [LivewireJavaScriptAssets::class, 'source']);
    Route::get('/livewire/livewire.js.map', [LivewireJavaScriptAssets::class, 'maps']);
});
