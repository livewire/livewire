<?php

namespace Livewire\Features\SupportFileUploads;

use Aws\S3\Exception\S3Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\HttpException;

class S3MultipartUploadController implements HasMiddleware
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

        $totalSize = (int) $request->header('Upload-Length');

        if ($totalSize <= 0) {
            abort(400, 'Upload-Length header must be a positive integer.');
        }

        $maxBytes = FileUploadConfiguration::maxUploadSizeInBytes()
            ?? FileUploadConfiguration::chunkAbsoluteMaxBytes();

        if ($totalSize > $maxBytes) {
            abort(413, "Upload-Length ({$totalSize} bytes) exceeds the maximum allowed size ({$maxBytes} bytes).");
        }

        $name = $this->decodeUploadName($request->header('Upload-Name', ''));
        $type = $request->header('Upload-Type', 'application/octet-stream');

        $file = UploadedFile::fake()->create($name, $totalSize / 1024, $type);
        $hash = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);
        $key = FileUploadConfiguration::path($hash);

        $partSize = FileUploadConfiguration::chunkSizeForS3();
        $numParts = (int) ceil($totalSize / $partSize);

        if ($numParts > 10_000) {
            abort(413, 'File requires more than 10,000 parts.');
        }

        $client = FileUploadConfiguration::s3Client();
        $bucket = FileUploadConfiguration::s3Bucket();

        $created = $client->createMultipartUpload([
            'Bucket' => $bucket,
            'Key' => $key,
            'ContentType' => $type ?: 'application/octet-stream',
        ]);

        $uploadId = $created['UploadId'];

        // Persist a server-side manifest so the client can't lie about
        // which S3 key the parts belong to or how many parts there are.
        $manifestKey = FileUploadConfiguration::path("multipart-manifests/{$uploadId}.json");
        FileUploadConfiguration::storage()->put($manifestKey, json_encode([
            'uploadId' => $uploadId,
            'key' => $key,
            'hash' => $hash,
            'name' => $name,
            'size' => $totalSize,
            'type' => $type,
            'partSize' => $partSize,
            'numParts' => $numParts,
            'created_at' => now()->timestamp,
        ]));

        $expiry = now()->addMinutes(FileUploadConfiguration::chunkMaxUploadTime());

        return response()->json([
            'uploadId' => $uploadId,
            'partSize' => $partSize,
            'numParts' => $numParts,
            'signPartUrl' => URL::temporarySignedRoute(
                'livewire.s3-multipart-sign-part', $expiry,
                ['uploadId' => $uploadId],
            ),
            'completeUrl' => URL::temporarySignedRoute(
                'livewire.s3-multipart-complete', $expiry,
                ['uploadId' => $uploadId],
            ),
            'abortUrl' => URL::temporarySignedRoute(
                'livewire.s3-multipart-abort', $expiry,
                ['uploadId' => $uploadId],
            ),
        ]);
    }

    public function signPart(Request $request, string $uploadId)
    {
        abort_unless($request->hasValidSignatureWhileIgnoring(['partNumber']), 401);

        $manifest = $this->loadManifest($uploadId);
        $partNumber = (int) $request->query('partNumber');

        if ($partNumber < 1 || $partNumber > $manifest['numParts']) {
            abort(400, "Part number {$partNumber} is out of range (1–{$manifest['numParts']}).");
        }

        $cmd = FileUploadConfiguration::s3Client()->getCommand('UploadPart', [
            'Bucket' => FileUploadConfiguration::s3Bucket(),
            'Key' => $manifest['key'],
            'UploadId' => $uploadId,
            'PartNumber' => $partNumber,
        ]);

        $url = (string) FileUploadConfiguration::s3Client()
            ->createPresignedRequest($cmd, '+1 hour')
            ->getUri();

        return response()->json([
            'url' => $url,
            'partNumber' => $partNumber,
        ]);
    }

    public function complete(Request $request, string $uploadId)
    {
        abort_unless($request->hasValidSignature(), 401);

        $manifest = $this->loadManifest($uploadId);

        $parts = collect($request->input('parts', []))
            ->map(fn ($p) => ['PartNumber' => (int) $p['partNumber'], 'ETag' => $p['etag']])
            ->sortBy('PartNumber')
            ->values()
            ->all();

        if (count($parts) !== $manifest['numParts']) {
            abort(400, 'Part count mismatch: expected ' . $manifest['numParts'] . ', got ' . count($parts) . '.');
        }

        try {
            FileUploadConfiguration::s3Client()->completeMultipartUpload([
                'Bucket' => FileUploadConfiguration::s3Bucket(),
                'Key' => $manifest['key'],
                'UploadId' => $uploadId,
                'MultipartUpload' => ['Parts' => $parts],
            ]);
        } catch (S3Exception $e) {
            $this->cleanupManifest($uploadId);
            throw new HttpException(422, 'Multipart completion failed: ' . $e->getAwsErrorCode());
        }

        $this->cleanupManifest($uploadId);

        return response()->json([
            'path' => TemporaryUploadedFile::signPath($manifest['hash']),
        ]);
    }

    public function abort(Request $request, string $uploadId)
    {
        abort_unless($request->hasValidSignature(), 401);

        $manifest = $this->loadManifest($uploadId);

        try {
            FileUploadConfiguration::s3Client()->abortMultipartUpload([
                'Bucket' => FileUploadConfiguration::s3Bucket(),
                'Key' => $manifest['key'],
                'UploadId' => $uploadId,
            ]);
        } catch (S3Exception $e) {
            // Already aborted or never existed — fine
        }

        $this->cleanupManifest($uploadId);

        return response('', 204);
    }

    public function listParts(Request $request, string $uploadId)
    {
        abort_unless($request->hasValidSignature(), 401);

        $manifest = $this->loadManifest($uploadId);

        $result = FileUploadConfiguration::s3Client()->listParts([
            'Bucket' => FileUploadConfiguration::s3Bucket(),
            'Key' => $manifest['key'],
            'UploadId' => $uploadId,
        ]);

        return response()->json([
            'parts' => collect($result['Parts'] ?? [])
                ->map(fn ($p) => [
                    'partNumber' => (int) $p['PartNumber'],
                    'size' => (int) $p['Size'],
                    'etag' => $p['ETag'],
                ])->values()->all(),
        ]);
    }

    protected function loadManifest(string $uploadId): array
    {
        $manifestKey = FileUploadConfiguration::path("multipart-manifests/{$uploadId}.json");

        $storage = FileUploadConfiguration::storage();

        if (! $storage->exists($manifestKey)) {
            abort(404, 'Upload session not found.');
        }

        return json_decode($storage->get($manifestKey), true);
    }

    protected function cleanupManifest(string $uploadId): void
    {
        $manifestKey = FileUploadConfiguration::path("multipart-manifests/{$uploadId}.json");

        FileUploadConfiguration::storage()->delete($manifestKey);
    }

    protected function decodeUploadName(string $encoded): string
    {
        if ($encoded === '') return 'unknown';

        $decoded = base64_decode($encoded, true);

        return $decoded === false ? 'unknown' : $decoded;
    }
}
