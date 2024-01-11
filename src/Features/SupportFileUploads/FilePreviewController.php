<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Routing\Controllers\HasMiddleware;
use Livewire\Drawer\Utils;

class FilePreviewController implements HasMiddleware
{
    public static array $middleware = ['web'];

    public static function middleware()
    {
        return static::$middleware;
    }

    public function handle($filename)
    {
        abort_unless(request()->hasValidSignature(), 401);

        return Utils::pretendPreviewResponseIsPreviewFile($filename);
    }
}
