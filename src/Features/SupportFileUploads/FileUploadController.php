<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileUploadController implements HasMiddleware
{
    public static function middleware()
    {
        return array_map(fn ($middleware) => new Middleware($middleware), array_merge(
            ['web'],
            (array) FileUploadConfiguration::middleware(),
        ));
    }

    public function handle()
    {
        abort_unless(request()->hasValidSignature(), 401);

        $disk = FileUploadConfiguration::disk();

        $filePaths = $this->validateAndStore(request('files'), $disk);

        return ['paths' => $filePaths];
    }

    public function validateAndStore($files, $disk)
    {
        Validator::make(['files' => $files], [
            'files.*' => FileUploadConfiguration::rules()
        ])->validate();

        $fileHashPaths = collect($files)->map(function ($file) use ($disk) {
            $filenameRequiresTruncationBeforeStorage = TemporaryUploadedFile::filenameRequiresTruncationBeforeEmbedding($file);
            $hashedFilename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);

            if ($filenameRequiresTruncationBeforeStorage) {
                // Our filename has required truncation before storage, due to being too long for encoding whilst
                // staying within filesystem filename size limits. We will write the original filename pre-truncation
                // to the directory of truncated files so that we can reference it if the developer wants to access
                // the original client name.
                Storage::disk($disk)->put(
                    FileUploadConfiguration::truncatedFilenamesMetaPath().'/'.$hashedFilename,
                    $file->getClientOriginalName(),
                );
            }

            return $file->storeAs('/'.FileUploadConfiguration::path(), $hashedFilename, [
                'disk' => $disk
            ]);
        });

        // Strip out the temporary upload directory from the paths.
        return $fileHashPaths->map(function ($path) { return str_replace(FileUploadConfiguration::path('/'), '', $path); });
    }
}
