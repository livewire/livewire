<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Validator;

class ChunkedUploadController extends FileUploadController
{
    public static function middleware()
    {
        // Large files arrive as many rapid-fire requests, so the default
        // one-per-file throttle would starve them...
        if (is_null(config('livewire.temporary_file_upload.middleware'))) {
            return array_map(
                fn ($middleware) => new \Illuminate\Routing\Controllers\Middleware($middleware),
                array_merge(static::$defaultMiddleware, ['throttle:600,1'])
            );
        }

        return parent::middleware();
    }

    public function handleChunk()
    {
        abort_unless(request()->hasValidSignature(), 401);

        // The upload's fingerprint, chunk count, and chunk size all come from
        // the signed reference the server issued at plan time — never from
        // request input the client could tamper with...
        $capability = ChunkedUpload::verifyCapability(request('id'));

        abort_if($capability === false, 403, 'Invalid chunk upload reference.');

        [$fingerprint, $totalChunks, $chunkSize] = $capability;

        // Individual chunks only need transport-level validation — the
        // configured upload rules run against the assembled file below...
        Validator::make(request()->all(), [
            'index' => 'required|integer|min:0|max:'.($totalChunks - 1),
            'chunk' => 'required|file|max:'.((int) ceil($chunkSize / 1024) + 1),
            'name' => 'required|string',
            'type' => 'nullable|string',
        ])->validate();

        ChunkedUpload::storeChunk($fingerprint, (int) request('index'), request()->file('chunk'));

        $received = ChunkedUpload::receivedChunks($fingerprint);

        if (count($received) < $totalChunks) {
            return ['complete' => false, 'received' => $received];
        }

        $path = ChunkedUpload::assemble($fingerprint, [
            'name' => request('name'),
            'type' => request('type'),
        ], FileUploadConfiguration::disk());

        return ['complete' => true, 'paths' => [$path]];
    }
}
