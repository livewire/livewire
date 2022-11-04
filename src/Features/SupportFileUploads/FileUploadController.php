<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Support\Facades\Validator;

class FileUploadController
{
    public function getMiddleware()
    {
        return [[
            'middleware' => FileUploadConfiguration::middleware(),
            'options' => [],
        ]];
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
            $filename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);

            return $file->storeAs('/'.FileUploadConfiguration::path(), $filename, [
                'disk' => $disk
            ]);
        });

        // Strip out the temporary upload directory from the paths.
        return $fileHashPaths->map(function ($path) { return str_replace(FileUploadConfiguration::path('/'), '', $path); });
    }
}
