<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ChunkedUploadController implements HasMiddleware
{
    public static array $defaultMiddleware = ['web'];

    public static function middleware()
    {
        $middleware = (array) FileUploadConfiguration::middleware();

        foreach (array_reverse(static::$defaultMiddleware) as $defaultMiddleware) {
            if (! in_array($defaultMiddleware, $middleware)) {
                array_unshift($middleware, $defaultMiddleware);
            }
        }

        return array_map(fn ($middleware) => new Middleware($middleware), $middleware);
    }

    public function init(Request $request)
    {
        abort_unless($request->hasValidSignature(), 401);

        $totalSize = (int) $request->header('Upload-Length');
        $transferId = Str::random(40);

        $storage = FileUploadConfiguration::storage();
        $dir = FileUploadConfiguration::path("chunks/{$transferId}");

        $storage->put("{$dir}/manifest.json", json_encode([
            'size' => $totalSize,
            'offset' => 0,
            'name' => $request->header('Upload-Name', 'unknown'),
            'created_at' => now()->timestamp,
        ]));

        $expiry = now()->addDay();

        return response()->json([
            'transferId' => $transferId,
            'patchUrl' => URL::signedRoute('livewire.chunk-upload-patch', ['transferId' => $transferId], $expiry),
            'offsetUrl' => URL::signedRoute('livewire.chunk-upload-offset', ['transferId' => $transferId], $expiry),
        ]);
    }

    public function patch(Request $request, string $transferId)
    {
        abort_unless($request->hasValidSignature(), 401);

        $storage = FileUploadConfiguration::storage();
        $dir = FileUploadConfiguration::path("chunks/{$transferId}");

        abort_unless($storage->exists("{$dir}/manifest.json"), 404);

        $manifest = json_decode($storage->get("{$dir}/manifest.json"), true);
        $clientOffset = (int) $request->header('Upload-Offset');

        abort_if($clientOffset !== $manifest['offset'], 409);

        $chunkPath = "{$dir}/data";
        $content = $request->getContent();
        $bytesWritten = strlen($content);

        if ($manifest['offset'] === 0) {
            $storage->put($chunkPath, $content);
        } else {
            $storage->append($chunkPath, $content);
        }

        $newOffset = $manifest['offset'] + $bytesWritten;
        $manifest['offset'] = $newOffset;
        $storage->put("{$dir}/manifest.json", json_encode($manifest));

        $headers = [
            'Upload-Offset' => $newOffset,
        ];

        if ($newOffset >= $manifest['size']) {
            $finalPath = $this->finalizeUpload($transferId, $dir, $storage, $manifest);
            $headers['Upload-Complete'] = 'true';
            $headers['X-Signed-Path'] = $finalPath;
        }

        return response('', 204, $headers);
    }

    public function offset(Request $request, string $transferId)
    {
        abort_unless($request->hasValidSignature(), 401);

        $storage = FileUploadConfiguration::storage();
        $dir = FileUploadConfiguration::path("chunks/{$transferId}");

        abort_unless($storage->exists("{$dir}/manifest.json"), 404);

        $manifest = json_decode($storage->get("{$dir}/manifest.json"), true);

        return response()->json([
            'offset' => $manifest['offset'],
            'size' => $manifest['size'],
        ]);
    }

    protected function finalizeUpload($transferId, $dir, $storage, $manifest)
    {
        $extension = pathinfo($manifest['name'], PATHINFO_EXTENSION);
        $tmpName = Str::random(40) . ($extension ? ".{$extension}" : '');
        $finalPath = FileUploadConfiguration::path($tmpName);

        $storage->move("{$dir}/data", $finalPath);

        $storage->put("{$finalPath}.json", json_encode([
            'name' => $manifest['name'],
        ]));

        $storage->deleteDirectory($dir);

        return TemporaryUploadedFile::signPath($tmpName);
    }
}
