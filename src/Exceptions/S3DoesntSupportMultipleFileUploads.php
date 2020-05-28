<?php

namespace Livewire\Exceptions;

class S3DoesntSupportMultipleFileUploads extends \Exception
{
    use BypassViewHandler;

    public function __construct()
    {
        return parent::__construct(
            "S3 temporary file upload driver only supports single file uploads. Remove the [multiple] HTML attribute from your input tag."
        );
    }
}
