<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Drawer\Utils;

class FilePreviewController
{
    public function handle($filename)
    {
        abort_unless(request()->hasValidSignature(), 401);

        return Utils::pretendResponseIsFile(
            FileUploadConfiguration::storage()->path(FileUploadConfiguration::path($filename)),
            FileUploadConfiguration::mimeType($filename)
        );
    }
}

