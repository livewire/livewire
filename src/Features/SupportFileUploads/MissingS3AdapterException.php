<?php

namespace Livewire\Features\SupportFileUploads;

use Exception;
use Livewire\Exceptions\BypassViewHandler;

class MissingS3AdapterException extends Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        parent::__construct(
            'Livewire\'s temporary file upload disk is set to S3, but the Flysystem S3 adapter is not installed. Run: composer require league/flysystem-aws-s3-v3 "^3.0"'
        );
    }
}
