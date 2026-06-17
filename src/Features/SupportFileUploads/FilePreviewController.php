<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Livewire\Drawer\Utils;
use Symfony\Component\HttpFoundation\HeaderUtils;

class FilePreviewController implements HasMiddleware
{
    public static array $middleware = ['web'];

    public static function middleware()
    {
        return array_map(fn ($middleware) => new Middleware($middleware), static::$middleware);
    }

    public function handle($filename)
    {
        abort_unless(request()->hasValidSignature(), 401);

        $response = Utils::pretendPreviewResponseIsPreviewFile($filename);

        // Cache hit
        if ($response->getStatusCode() === 304) {
            return $response;
        }

        $temporaryFile = new TemporaryUploadedFile($filename, FileUploadConfiguration::disk());
        $originalName = $temporaryFile->getClientOriginalName();

        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $originalName),
        );

        return $response;
    }
}
