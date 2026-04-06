<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ChunkedUploadController implements HasMiddleware
{
    public static array $defaultMiddleware = ['web'];

    public static function middleware()
    {
        $middleware = (array) FileUploadConfiguration::chunkMiddleware();

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

        // Refuse to handle chunked uploads on disks that don't support
        // direct file system access (e.g., S3). The trait should already
        // prevent this from being called, but be defensive.
        if (FileUploadConfiguration::isUsingS3()) {
            abort(400, 'Chunked uploads are not supported on the S3 disk.');
        }

        $totalSize = (int) $request->header('Upload-Length');

        if ($totalSize <= 0) {
            abort(400, 'Upload-Length header must be a positive integer.');
        }

        // Reject uploads that exceed the configured max size up front so we
        // never commit storage for an upload we know we'll reject later.
        $maxBytes = FileUploadConfiguration::maxUploadSizeInBytes();
        if ($maxBytes !== null && $totalSize > $maxBytes) {
            abort(413, "Upload-Length ({$totalSize} bytes) exceeds the maximum allowed size ({$maxBytes} bytes).");
        }

        $name = $this->decodeUploadName($request->header('Upload-Name', ''));
        $transferId = Str::random(40);

        $storage = FileUploadConfiguration::storage();
        $manifestRelative = "chunks/{$transferId}/manifest.json";
        $storage->put(FileUploadConfiguration::path($manifestRelative), json_encode([
            'size' => $totalSize,
            'offset' => 0,
            'name' => $name,
            'created_at' => now()->timestamp,
        ]));

        $expiry = now()->addMinutes(FileUploadConfiguration::chunkMaxUploadTime());

        return response()->json([
            'transferId' => $transferId,
            'patchUrl' => URL::temporarySignedRoute(
                'livewire.chunk-upload-patch',
                $expiry,
                ['transferId' => $transferId],
            ),
            'offsetUrl' => URL::temporarySignedRoute(
                'livewire.chunk-upload-offset',
                $expiry,
                ['transferId' => $transferId],
            ),
        ]);
    }

    public function patch(Request $request, string $transferId)
    {
        abort_unless($request->hasValidSignature(), 401);
        $this->assertValidTransferId($transferId);

        $storage = FileUploadConfiguration::storage();
        $manifestRelative = FileUploadConfiguration::path("chunks/{$transferId}/manifest.json");
        $dataRelative = FileUploadConfiguration::path("chunks/{$transferId}/data");

        if (! $storage->exists($manifestRelative)) {
            abort(404);
        }

        $clientOffset = (int) $request->header('Upload-Offset');
        $content = $request->getContent();
        $bytesIncoming = strlen($content);

        // Reject obviously bad chunk sizes up front
        $chunkSize = FileUploadConfiguration::chunkSize();
        if ($chunkSize !== null && $bytesIncoming > $chunkSize) {
            abort(413, "Chunk size ({$bytesIncoming} bytes) exceeds configured chunk_size ({$chunkSize} bytes).");
        }

        // Atomically read manifest, validate offset, write chunk, update manifest.
        // Holding the lock for the whole operation prevents race conditions on
        // concurrent PATCHes for the same transfer (which the JS shouldn't do,
        // but defensive coding matters).
        $manifestPath = $storage->path($manifestRelative);
        $dataPath = $storage->path($dataRelative);

        $fp = fopen($manifestPath, 'c+');
        if ($fp === false) {
            abort(500, 'Could not open upload manifest.');
        }

        try {
            if (! flock($fp, LOCK_EX)) {
                abort(500, 'Could not lock upload manifest.');
            }

            $rawManifest = stream_get_contents($fp);
            $manifest = json_decode($rawManifest, true);

            if (! is_array($manifest) || ! isset($manifest['size'], $manifest['offset'])) {
                abort(500, 'Upload manifest is corrupt.');
            }

            // Offset mismatch — client and server disagree about where we are
            if ($clientOffset !== (int) $manifest['offset']) {
                abort(409);
            }

            // Refuse to write more bytes than we said we'd accept
            if (($manifest['offset'] + $bytesIncoming) > $manifest['size']) {
                abort(413, 'Chunk would exceed declared upload length.');
            }

            // Append the chunk to the data file
            $dataFp = fopen($dataPath, 'ab');
            if ($dataFp === false) {
                abort(500, 'Could not open upload data file.');
            }

            $written = fwrite($dataFp, $content);
            fclose($dataFp);

            if ($written !== $bytesIncoming) {
                abort(500, 'Failed to write full chunk to disk.');
            }

            $manifest['offset'] += $bytesIncoming;

            // Write the updated manifest back atomically
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($manifest));
            fflush($fp);

            $newOffset = $manifest['offset'];
            $isComplete = $newOffset >= $manifest['size'];
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        $headers = [
            'Upload-Offset' => $newOffset,
        ];

        if ($isComplete) {
            $result = $this->finalizeUpload($transferId, $manifest, $storage);

            if ($result instanceof \Symfony\Component\HttpFoundation\Response) {
                // Validation failed — return the response directly
                return $result;
            }

            $headers['Upload-Complete'] = 'true';
            $headers['X-Signed-Path'] = $result;
        }

        return response('', 204, $headers);
    }

    public function offset(Request $request, string $transferId)
    {
        abort_unless($request->hasValidSignature(), 401);
        $this->assertValidTransferId($transferId);

        $storage = FileUploadConfiguration::storage();
        $manifestRelative = FileUploadConfiguration::path("chunks/{$transferId}/manifest.json");

        if (! $storage->exists($manifestRelative)) {
            abort(404);
        }

        $manifest = json_decode($storage->get($manifestRelative), true);

        return response()->json([
            'offset' => (int) ($manifest['offset'] ?? 0),
            'size' => (int) ($manifest['size'] ?? 0),
        ], 200, [
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Move the assembled chunks to a temporary upload file, validate it
     * against the configured rules, and return a signed path.
     *
     * Returns a Symfony Response on validation failure (so the caller can
     * return it directly), or a string signed path on success.
     */
    protected function finalizeUpload(string $transferId, array $manifest, $storage)
    {
        $originalName = $manifest['name'] ?? 'unknown';
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Match the existing temp filename convention used by FileUploadConfiguration::storeTemporaryFile
        $hash = Str::random(40);
        $tmpName = $extension ? "{$hash}.{$extension}" : $hash;

        $sourceRelative = FileUploadConfiguration::path("chunks/{$transferId}/data");
        $destRelative = FileUploadConfiguration::path($tmpName);
        $metaRelative = FileUploadConfiguration::path($tmpName . '.json');
        $chunkDirRelative = FileUploadConfiguration::path("chunks/{$transferId}");

        // Move the assembled file into livewire-tmp
        $storage->move($sourceRelative, $destRelative);

        // Run validation against the configured rules. We construct an
        // UploadedFile pointing at the assembled file so Laravel can apply
        // mime/size rules just like a normal upload would.
        $assembledPath = $storage->path($destRelative);
        $uploadedFile = new UploadedFile(
            $assembledPath,
            $originalName,
            null, // mime — let Laravel detect from contents
            null, // error
            true, // test mode — bypass is_uploaded_file() check
        );

        $validator = Validator::make(
            ['file' => $uploadedFile],
            ['file' => FileUploadConfiguration::rules()],
        );

        if ($validator->fails()) {
            // Clean up everything we created so we don't leak storage on failed validation
            $storage->delete($destRelative);
            $storage->deleteDirectory($chunkDirRelative);

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => collect($validator->errors()->toArray())
                    ->mapWithKeys(fn ($messages, $key) => ['files.0' => $messages])
                    ->all(),
            ], 422);
        }

        // Validation passed — write the meta file and clean up the chunks dir
        $storage->put($metaRelative, json_encode([
            'name' => $originalName,
            'type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'hash' => $tmpName,
        ]));

        $storage->deleteDirectory($chunkDirRelative);

        return TemporaryUploadedFile::signPath($tmpName);
    }

    /**
     * Defensive check that the transfer ID matches our expected format
     * (40 alphanumeric chars from Str::random). Prevents any path-traversal
     * weirdness even though Laravel's router should already block it.
     */
    protected function assertValidTransferId(string $transferId): void
    {
        if (! preg_match('/^[A-Za-z0-9]{40}$/', $transferId)) {
            abort(400, 'Invalid transfer ID format.');
        }
    }

    /**
     * The Upload-Name header is base64-encoded by the JS client to avoid
     * InvalidCharacterError on filenames containing non-ASCII characters.
     */
    protected function decodeUploadName(string $encoded): string
    {
        if ($encoded === '') return 'unknown';

        $decoded = base64_decode($encoded, true);

        return $decoded === false ? 'unknown' : $decoded;
    }
}
