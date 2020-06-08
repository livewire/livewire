<?php

namespace Livewire\Controllers;


use Livewire\TemporaryUploadedFile;
use Livewire\FileUploadConfiguration;
use Illuminate\Support\Facades\Validator;

class FileUploadHandler
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

            return $file->storeAs('/'. rtrim(FileUploadConfiguration::directory(), '/'), $filename, [
                'disk' => $disk
            ]);
        });

        // Strip out the livewire-tmp directory from the paths.
        return $fileHashPaths->map(function ($path) { return str_replace(FileUploadConfiguration::directory(), '', $path); });
    }
}
