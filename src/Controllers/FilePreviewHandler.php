<?php

namespace Livewire\Controllers;

use Livewire\FileUploadConfiguration;

class FilePreviewHandler
{
    use CanPretendToBeAFile;

    public function handle($filename)
    {
        abort_unless(request()->hasValidSignature(), 401);

        $file = FileUploadConfiguration::path($filename);
        return $this->pretendResponseIsFile(
            FileUploadConfiguration::storage()->get($file),
            FileUploadConfiguration::storage()->lastModified($file),
            FileUploadConfiguration::mimeType($filename)
        );
    }
}
