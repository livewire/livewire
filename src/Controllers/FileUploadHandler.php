<?php

namespace Livewire\Controllers;

use Illuminate\Support\Facades\Validator;

class FileUploadHandler
{
    public function handle()
    {
        abort_unless(request()->hasValidSignature(), 401);

        $disk = config('livewire.temporary_file_upload.disk') ?: config('filsystems.default');

        $filePaths = $this->validateAndStore(request('files'), $disk);

        return ['paths' => $filePaths];
    }

    public function validateAndStore($files, $disk)
    {
        Validator::make(['files' => $files], [
            'files.*' => 'required|'.(config('livewire.temporary_file_upload.rules') ?: 'file|max:12288') // Max: 12MB
        ])->validate();

        $fileHashPaths = collect($files)->map->store('/tmp', [
            'disk' => $disk
        ]);

        return $fileHashPaths->map(function ($path) { return str_replace('tmp/', '', $path); });
    }
}
