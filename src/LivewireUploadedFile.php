<?php

namespace Livewire;

use Illuminate\Http\UploadedFile;

class LivewireUploadedFile extends UploadedFile
{
    public function isValid()
    {
        $isOk = UPLOAD_ERR_OK === $this->getError();

        return $isOk;
    }
}
