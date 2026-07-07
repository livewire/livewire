<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
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

        // The file was already assembled on a previous request whose response
        // never made it back (or this is a duplicate/reload). Hand back the
        // remembered result instead of re-stitching anything...
        if ($path = ChunkedUpload::completedPath($fingerprint)) {
            return ['complete' => true, 'paths' => [$path]];
        }

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

        return $this->assembleOnce($fingerprint, $totalChunks);
    }

    // Only one request may assemble a given fingerprint. Concurrent "final"
    // chunks (two tabs, a double-click) would otherwise both stitch the file —
    // racing each other's directory deletion into a truncated or empty result.
    protected function assembleOnce($fingerprint, $totalChunks)
    {
        $lock = null;

        try {
            $lock = Cache::lock('livewire-chunk-assemble:'.$fingerprint, 60);
        } catch (\Throwable $e) {
            // The configured cache store doesn't support locks — fall through
            // and rely on the tombstone + completeness guards below...
        }

        if ($lock) {
            try {
                $lock->block(15);
            } catch (LockTimeoutException $e) {
                // Another request is assembling right now; report progress and
                // let the client's resync loop pick up the finished result...
                return ['complete' => false, 'received' => ChunkedUpload::receivedChunks($fingerprint)];
            }
        }

        try {
            if ($path = ChunkedUpload::completedPath($fingerprint)) {
                return ['complete' => true, 'paths' => [$path]];
            }

            abort_unless(
                ChunkedUpload::hasAllChunks($fingerprint, $totalChunks),
                422, 'The chunked upload is incomplete.'
            );

            $path = ChunkedUpload::assemble($fingerprint, [
                'name' => request('name'),
                'type' => request('type'),
            ], FileUploadConfiguration::disk());

            return ['complete' => true, 'paths' => [$path]];
        } finally {
            if ($lock) $lock->release();
        }
    }
}
