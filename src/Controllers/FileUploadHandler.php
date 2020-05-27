<?php

namespace Livewire\Controllers;

use Illuminate\Support\Facades\Validator;
use Livewire\FileUploadConfiguration;

class FileUploadHandler
{
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
            'files.*' => 'required|'.FileUploadConfiguration::rules()
        ])->validate();

        $fileHashPaths = collect($files)->map->store('/tmp', [
            'disk' => $disk
        ]);

        return $fileHashPaths->map(function ($path) { return str_replace('tmp/', '', $path); });
    }
}
