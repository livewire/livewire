<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;

class ChunkUploadController implements HasMiddleware
{
    public static function middleware()
    {
        $middleware = (array) FileUploadConfiguration::chunkMiddleware();

        foreach (array_reverse(FileUploadController::$defaultMiddleware) as $defaultMiddleware) {
            if (! in_array($defaultMiddleware, $middleware)) {
                array_unshift($middleware, $defaultMiddleware);
            }
        }

        return array_map(fn ($m) => new Middleware($m), $middleware);
    }

    public function handle()
    {
        abort_unless(request()->hasValidSignature(), 401);

        $validated = Validator::make(request()->all(), [
            'chunk' => ['required', 'file'],
            'uploadId' => ['required', 'string', 'uuid'],
            'chunkIndex' => ['required', 'integer', 'min:0'],
            'totalChunks' => ['required', 'integer', 'min:1'],
        ])->validate();

        $uploadId = $validated['uploadId'];
        $chunkIndex = (int) $validated['chunkIndex'];
        $totalChunks = (int) $validated['totalChunks'];

        abort_if($chunkIndex >= $totalChunks, 422, 'Chunk index exceeds total chunks.');

        $chunkedUploads = session()->get('livewire_chunked_uploads', []);
        abort_unless(array_key_exists($uploadId, $chunkedUploads), 403, 'Invalid upload ID.');

        $metadata = $chunkedUploads[$uploadId];

        $chunk = request()->file('chunk');

        $maxChunkSize = FileUploadConfiguration::maxChunkSize();
        abort_if($chunk->getSize() > $maxChunkSize, 422, 'Chunk exceeds maximum allowed size.');

        $disk = FileUploadConfiguration::disk();
        $storage = FileUploadConfiguration::storage();

        $chunkDir = FileUploadConfiguration::path('chunks/' . $uploadId);
        $chunkFilename = str_pad($chunkIndex, 6, '0', STR_PAD_LEFT);

        $chunk->storeAs('/' . $chunkDir, $chunkFilename, ['disk' => $disk]);

        $storedChunks = $storage->files($chunkDir);

        if (count($storedChunks) < $totalChunks) {
            return ['status' => 'partial', 'index' => $chunkIndex];
        }

        return $this->reassemble(
            $storage, $disk, $chunkDir, $totalChunks,
            $metadata['fileName'], (int) $metadata['fileSize'], $metadata['fileMimeType'],
            $uploadId
        );
    }

    protected function reassemble($storage, $disk, $chunkDir, $totalChunks, $fileName, $fileSize, $fileMimeType, $uploadId)
    {
        $tmpFile = tmpfile();
        $tmpPath = stream_get_meta_data($tmpFile)['uri'];

        try {
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $chunkDir . '/' . str_pad($i, 6, '0', STR_PAD_LEFT);
                $stream = $storage->readStream($chunkPath);
                stream_copy_to_stream($stream, $tmpFile);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            fflush($tmpFile);

            $actualSize = fstat($tmpFile)['size'];

            if ($actualSize !== $fileSize) {
                $storage->deleteDirectory($chunkDir);
                abort(422, 'Reassembled file size does not match expected size.');
            }

            $assembledFile = new UploadedFile($tmpPath, $fileName, $fileMimeType, null, true);

            Validator::make(['files' => [$assembledFile]], [
                'files.*' => FileUploadConfiguration::rules()
            ])->validate();

            $finalPath = FileUploadConfiguration::storeTemporaryFile($assembledFile, $disk);

            $storage->deleteDirectory($chunkDir);

            $uploads = session()->get('livewire_chunked_uploads', []);
            unset($uploads[$uploadId]);
            session()->put('livewire_chunked_uploads', $uploads);

            $stripped = str_replace(FileUploadConfiguration::path('/'), '', $finalPath);

            return ['path' => TemporaryUploadedFile::signPath($stripped)];
        } finally {
            fclose($tmpFile);
        }
    }
}
