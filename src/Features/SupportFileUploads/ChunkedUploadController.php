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

        $id = TemporaryUploadedFile::extractPathFromSignedPath((string) request('id'));

        abort_if($id === false || ! preg_match('/^[a-f0-9]{40}$/', $id), 403, 'Invalid chunk upload reference.');

        $index = (int) request('index');
        $total = (int) request('total');

        abort_if($index < 0 || $total < 1 || $index >= $total || $total > 10000, 422, 'Invalid chunk index.');

        // Individual chunks only need transport-level validation — the
        // configured upload rules run against the assembled file below...
        Validator::make(request()->all(), [
            'chunk' => 'required|file|max:'.((int) ceil(FileUploadConfiguration::chunkSize() / 1024) + 1),
            'name' => 'required|string',
        ])->validate();

        ChunkedUpload::storeChunk($id, $index, request()->file('chunk'));

        $received = ChunkedUpload::receivedChunks($id);

        if (count($received) < $total) {
            return ['complete' => false, 'received' => $received];
        }

        $path = ChunkedUpload::assemble($id, [
            'name' => request('name'),
            'type' => request('type'),
        ], FileUploadConfiguration::disk());

        return ['complete' => true, 'paths' => [$path]];
    }
}
