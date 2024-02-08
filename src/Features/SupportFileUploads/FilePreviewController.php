<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Livewire\Drawer\Utils;

class FilePreviewController implements HasMiddleware
{
    public static function middleware()
    {
        return array_map(fn ($middleware) => new Middleware($middleware), array_merge(
            ['web'],
            (array) FileUploadConfiguration::middleware(),
        ));
    }

    public function handle($filename)
    {
        abort_unless(request()->hasValidSignature(), 401);

        return Utils::pretendResponseIsFile(
            FileUploadConfiguration::storage()->path(FileUploadConfiguration::path($filename)),
            FileUploadConfiguration::mimeType($filename)
        );
    }
}
