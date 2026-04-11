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

        $created = FileUploadConfiguration::s3Client()->createMultipartUpload([
            'Bucket' => FileUploadConfiguration::s3Bucket(),
            'Key' => $key,
            'ContentType' => $type ?: 'application/octet-stream',
        ]);

        $uploadId = $created['UploadId'];
        $expiry = now()->addMinutes(FileUploadConfiguration::chunkMaxUploadTime());

        // All upload state is embedded in the signed URLs — no server-side
        // manifest needed. The signature prevents the client from tampering
        // with key, hash, or numParts.
        $params = compact('uploadId', 'key', 'hash', 'numParts', 'totalSize');

        return response()->json([
            'uploadId' => $uploadId,
            'partSize' => $partSize,
            'numParts' => $numParts,
            'signPartUrl' => URL::temporarySignedRoute(
                'livewire.s3-multipart-sign-part', $expiry, $params,
            ),
            'completeUrl' => URL::temporarySignedRoute(
                'livewire.s3-multipart-complete', $expiry, $params,
            ),
            'abortUrl' => URL::temporarySignedRoute(
                'livewire.s3-multipart-abort', $expiry, $params,
            ),
        ]);
    }

    public function signPart(Request $request, string $uploadId)
    {
        abort_unless($request->hasValidSignatureWhileIgnoring(['partNumber']), 401);

        $key = $request->query('key');
        $numParts = (int) $request->query('numParts');
        $partNumber = (int) $request->query('partNumber');

        if ($partNumber < 1 || $partNumber > $numParts) {
            abort(400, "Part number {$partNumber} is out of range (1–{$numParts}).");
        }

        $cmd = FileUploadConfiguration::s3Client()->getCommand('UploadPart', [
            'Bucket' => FileUploadConfiguration::s3Bucket(),
            'Key' => $key,
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

        $key = $request->query('key');
        $hash = $request->query('hash');
        $numParts = (int) $request->query('numParts');
        $claimedSize = (int) $request->query('totalSize');

        $client = FileUploadConfiguration::s3Client();
        $bucket = FileUploadConfiguration::s3Bucket();

        // Fetch ETags server-side via ListParts so the browser never needs
        // to read S3 response headers — no CORS ExposeHeaders required.
        $listed = $client->listParts([
            'Bucket' => $bucket,
            'Key' => $key,
            'UploadId' => $uploadId,
        ]);

        $parts = collect($listed['Parts'] ?? [])
            ->map(fn ($p) => ['PartNumber' => (int) $p['PartNumber'], 'ETag' => $p['ETag']])
            ->sortBy('PartNumber')
            ->values()
            ->all();

        if (count($parts) !== $numParts) {
            abort(400, 'Part count mismatch: expected ' . $numParts . ', got ' . count($parts) . '.');
        }

        try {
            $client->completeMultipartUpload([
                'Bucket' => $bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
                'MultipartUpload' => ['Parts' => $parts],
            ]);
        } catch (S3Exception $e) {
            throw new HttpException(422, 'Multipart completion failed: ' . $e->getAwsErrorCode());
        }

        // Verify the assembled object's actual size matches the claimed size
        // from init. This catches a malicious client that lies about file size
        // to bypass pre-transfer max: validation then uploads different bytes.
        $head = $client->headObject(['Bucket' => $bucket, 'Key' => $key]);
        $actualSize = (int) $head['ContentLength'];

        if ($actualSize !== $claimedSize) {
            $client->deleteObject(['Bucket' => $bucket, 'Key' => $key]);
            abort(422, "Uploaded size ({$actualSize} bytes) does not match claimed size ({$claimedSize} bytes).");
        }

        return response()->json([
            'path' => TemporaryUploadedFile::signPath($hash),
        ]);
    }

    public function abort(Request $request, string $uploadId)
    {
        abort_unless($request->hasValidSignature(), 401);

        try {
            FileUploadConfiguration::s3Client()->abortMultipartUpload([
                'Bucket' => FileUploadConfiguration::s3Bucket(),
                'Key' => $request->query('key'),
                'UploadId' => $uploadId,
            ]);
        } catch (S3Exception $e) {
            // Already aborted or never existed — fine
        }

        return response('', 204);
    }

    public function listParts(Request $request, string $uploadId)
    {
        abort_unless($request->hasValidSignature(), 401);

        $result = FileUploadConfiguration::s3Client()->listParts([
            'Bucket' => FileUploadConfiguration::s3Bucket(),
            'Key' => $request->query('key'),
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

    protected function decodeUploadName(string $encoded): string
    {
        if ($encoded === '') return 'unknown';

        $decoded = base64_decode($encoded, true);

        return $decoded === false ? 'unknown' : $decoded;
    }
}
