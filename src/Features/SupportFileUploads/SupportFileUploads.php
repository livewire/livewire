<?php

namespace Livewire\Features\SupportFileUploads;

use function Livewire\on;
use Livewire\ComponentHook;
use Illuminate\Support\Facades\Route;
use Facades\Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl as GenerateSignedUploadUrlFacade;

class SupportFileUploads extends ComponentHook
{
    static function provide()
    {
        if (app()->runningUnitTests()) {
            // Don't actually generate S3 signedUrls during testing.
            GenerateSignedUploadUrlFacade::swap(new class extends GenerateSignedUploadUrl {
                public function forS3($file, $visibility = '') { return []; }
            });
        }

        app('livewire')->propertySynthesizer([
            FileUploadSynth::class,
        ]);

        on('call', function ($component, $method, $params, $addEffect, $earlyReturn) {
            if ($method === '_startUpload') {
                if (! method_exists($component, $method)) {
                    throw new MissingFileUploadsTraitException($component);
                }
            }
        });

        Route::post('/livewire/upload-file', [FileUploadController::class, 'handle'])
            ->name('livewire.upload-file');

        Route::get('/livewire/preview-file/{filename}', [FilePreviewController::class, 'handle'])
            ->name('livewire.preview-file');
    }
}
