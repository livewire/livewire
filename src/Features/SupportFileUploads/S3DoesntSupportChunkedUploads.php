<?php

namespace Livewire\Features\SupportFileUploads;

use Exception;
use Livewire\Exceptions\BypassViewHandler;

class S3DoesntSupportChunkedUploads extends Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        parent::__construct(
            'Chunked file uploads are not supported on the S3 temporary file upload disk. '
            . 'Either set [livewire.temporary_file_upload.chunk.size] to null, or use a local disk.'
        );
    }
}
