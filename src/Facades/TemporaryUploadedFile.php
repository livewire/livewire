<?php

namespace Livewire\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Livewire\TemporaryUploadedFile
 * @see \Livewire\TemporaryUploadedFile
 */
class TemporaryUploadedFile extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'livewire.temporary_uploaded_file';
    }
}
