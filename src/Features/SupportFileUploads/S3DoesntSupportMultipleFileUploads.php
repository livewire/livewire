<?php

namespace Livewire\Features\SupportFileUploads;

use Exception;
use Livewire\Exceptions\BypassViewHandler;

/**
 * @deprecated S3 now supports multiple file uploads (one presigned PUT per
 * file), so this exception is no longer thrown. Kept only so that apps and
 * tests referencing it don't fatal. It will be removed in a future release.
 */
class S3DoesntSupportMultipleFileUploads extends Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        parent::__construct(
            'S3 temporary file upload driver only supports single file uploads. Remove the [multiple] HTML attribute from your input tag.'
        );
    }
}
