<?php

namespace Livewire\Features\SupportFileUploads;

use Livewire\Exceptions\BypassViewHandler;

class FileNotPreviewableException extends \Exception
{
    use BypassViewHandler;

    public function __construct(TemporaryUploadedFile $file)
    {
        parent::__construct(
            "File with extension \"{$file->guessExtension()}\" is not previewable. See the livewire.temporary_file_upload.preview_mimes config."
        );
    }
}
