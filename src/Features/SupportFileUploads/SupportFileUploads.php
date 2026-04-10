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

        Route::post(EndpointResolver::chunkInitPath(), [ChunkedUploadController::class, 'init'])
            ->name('livewire.chunk-upload-init');

        Route::patch(EndpointResolver::chunkPatchPath(), [ChunkedUploadController::class, 'patch'])
            ->name('livewire.chunk-upload-patch');

        Route::get(EndpointResolver::chunkOffsetPath(), [ChunkedUploadController::class, 'offset'])
            ->name('livewire.chunk-upload-offset');

        Route::post(EndpointResolver::s3MultipartInitPath(), [S3MultipartUploadController::class, 'init'])
            ->name('livewire.s3-multipart-init');

        Route::get(EndpointResolver::s3MultipartSignPartPath(), [S3MultipartUploadController::class, 'signPart'])
            ->name('livewire.s3-multipart-sign-part');

        Route::post(EndpointResolver::s3MultipartCompletePath(), [S3MultipartUploadController::class, 'complete'])
            ->name('livewire.s3-multipart-complete');

        Route::post(EndpointResolver::s3MultipartAbortPath(), [S3MultipartUploadController::class, 'abort'])
            ->name('livewire.s3-multipart-abort');

        Route::get(EndpointResolver::s3MultipartListPartsPath(), [S3MultipartUploadController::class, 'listParts'])
            ->name('livewire.s3-multipart-list-parts');
    }
}
