<?php

namespace Livewire\Features\SupportFileUploads;

use function Livewire\on;
use Livewire\ComponentHook;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use Livewire\Facades\GenerateSignedUploadUrlFacade;

class SupportFileUploads extends ComponentHook
{
    static $uploadRoute;

    static $previewRoute;

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

        app()->booted(function () {
            if (! static::$uploadRoute && ! static::uploadRouteExists()) {
                static::setUploadRoute(function ($handle) {
                    return Route::post(EndpointResolver::uploadPath(), $handle);
                });
            }

            if (! static::$previewRoute && ! static::previewRouteExists()) {
                static::setPreviewRoute(function ($handle) {
                    return Route::get(EndpointResolver::previewPath(), $handle);
                });
            }
        });
    }

    static function setUploadRoute($callback)
    {
        $route = $callback([FileUploadController::class, 'handle']);

        if (! str($route->getName())->endsWith('livewire.upload-file')) {
            $route->name('livewire.upload-file');
        }

        static::$uploadRoute = $route;
    }

    static function setPreviewRoute($callback)
    {
        $route = $callback([FilePreviewController::class, 'handle']);

        if (! str($route->getName())->endsWith('livewire.preview-file')) {
            $route->name('livewire.preview-file');
        }

        static::$previewRoute = $route;
    }

    static function uploadRouteExists()
    {
        return static::findRouteByName('livewire.upload-file') !== null;
    }

    static function previewRouteExists()
    {
        return static::findRouteByName('livewire.preview-file') !== null;
    }

    static function findRouteByName($name)
    {
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (str($route->getName())->endsWith($name)) {
                return $route;
            }
        }

        return null;
    }
}
