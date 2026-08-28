<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Livewire\Drawer\Utils;

class FilePreviewController implements HasMiddleware
{
    public static array $middleware = ['web'];

    public static function middleware()
    {
        return array_map(fn ($middleware) => new Middleware($middleware), static::$middleware);
    }

    public function handle($filename)
    {
        abort_unless(request()->hasValidRelativeSignature(), 401);

        $downloadName = request()->boolean('useOriginalFilename')
            ? (new TemporaryUploadedFile($filename, FileUploadConfiguration::disk()))->getClientOriginalName()
            : null;

        return Utils::pretendPreviewResponseIsPreviewFile($filename, $downloadName);
    }
}
