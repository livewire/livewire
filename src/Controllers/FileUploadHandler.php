<?php

namespace Livewire\Controllers;

use Illuminate\Support\Facades\Validator;

class FileUploadHandler
{
    public function handle()
    {
        abort_unless(request()->hasValidSignature(), 401);

        $disk = config('livewire.file_upload.disk') ?: config('filsystems.default');

        $hashes = $this->validateAndStore(request('files'), $disk);

        return ['paths' => $hashes];
    }

    public function validateAndStore($files, $disk)
    {
        Validator::make(['files' => $files], [
            'files.*' => 'required|'.(config('livewire.file_upload.rules') ?: 'file|max:12288')
        ])->validate();

        $hashes = collect($files)->map->store('/tmp', [
            'disk' => $disk
        ]);

        return $hashes->map(function ($hash) { return str_replace('tmp/', '', $hash); });
    }
}
