<?php

namespace Livewire\Features\SupportFileUploads;

use function Livewire\on;
use Livewire\ComponentHook;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Facades\GenerateSignedUploadUrlFacade;

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

        Route::post(EndpointResolver::uploadPath(), [FileUploadController::class, 'handle'])
            ->name('livewire.upload-file');

        Route::get(EndpointResolver::previewPath(), [FilePreviewController::class, 'handle'])
            ->name('livewire.preview-file');
    }
}
