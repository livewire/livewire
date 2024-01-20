<?php

namespace Livewire\Features\SupportFileUploads;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
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
            $filename = TemporaryUploadedFile::generateHashNameWithOriginalNameEmbedded($file);

            return $file->storeAs('/'.FileUploadConfiguration::path(), $filename, [
                'disk' => $disk
            ]);
        });

        // Strip out the temporary upload directory from the paths.
        return $fileHashPaths->map(function ($path) { return str_replace(FileUploadConfiguration::path('/'), '', $path); });
    }
}
