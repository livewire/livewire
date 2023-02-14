<?php

namespace Livewire\Features\SupportFileUploads;

use function Livewire\on;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\Component;
use Illuminate\Support\Facades\Route;
use Facades\Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl as GenerateSignedUploadUrlFacade;
use Livewire\ComponentHook;

class SupportFileUploads extends ComponentHook
{
    static function provide()
    {
        if (app()->runningUnitTests()) {
            // Don't actually generate S3 signedUrls during testing.
            // Can't use ::partialMock because it's not available in older versions of Laravel.
            $mock = \Mockery::mock(GenerateSignedUploadUrl::class);
            $mock->makePartial()->shouldReceive('forS3')->andReturn([]);
            GenerateSignedUploadUrlFacade::swap($mock);
        }

        app('livewire')->synth([
            FileUploadSynth::class,
        ]);

        Route::post('/livewire/upload-file', [FileUploadController::class, 'handle'])
            ->name('livewire.upload-file')
            ->middleware(config('livewire.middleware_group', ''));

        Route::get('/livewire/preview-file/{filename}', [FilePreviewController::class, 'handle'])
            ->name('livewire.preview-file')
            ->middleware(config('livewire.middleware_group', ''));
    }

    public function call($method)
    {
        if ($method === 'startUpload') {
            if (! method_exists($this->component, $method)) {
                throw new MissingFileUploadsTraitException($this->component);
            }
        }
    }
}
