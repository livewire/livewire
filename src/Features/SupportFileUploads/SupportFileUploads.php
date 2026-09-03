<?php

namespace Livewire\Features\SupportFileUploads;

use function Livewire\on;
use Livewire\ComponentHook;
use Illuminate\Support\Facades\Route;
use Livewire\Facades\GenerateSignedUploadUrlFacade;
use Livewire\Facades\LivewireEndpoint;

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

        on('call', function ($component, $method, $params, $componentContext, $earlyReturn) {
            if ($method === '_startUpload') {
                if (! method_exists($component, $method)) {
                    throw new MissingFileUploadsTraitException($component);
                }
            }
        });

        Route::post(LivewireEndpoint::uploadPath(), [FileUploadController::class, 'handle'])
            ->name('livewire.upload-file');

        Route::get(LivewireEndpoint::previewPath(), [FilePreviewController::class, 'handle'])
            ->name('livewire.preview-file');
    }
}
