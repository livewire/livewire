<?php

namespace Livewire;

use Illuminate\Support\Str;
use Illuminate\Http\Testing\File;

class LivewireNotYetUploadedFile extends File
{
    public function stillUploading()
    {
        return true;
    }

    public function finishedUploading()
    {
        return false;
    }

    public static function createFromLivewire($fileInfo)
    {
        return tap(new static($fileInfo['name'], tmpfile()), function ($file) use ($fileInfo) {
            $file->sizeToReport = $fileInfo['size'];
            $file->mimeTypeToReport = $fileInfo['type'];
        });
    }

    public static function canUnserialize($subject)
    {
        return Str::startsWith($subject, 'livewire-pending-file:')
            || Str::startsWith($subject, 'livewire-pending-files:');
    }

    public static function unserializeFromLivewireRequest($subject)
    {
        if (Str::startsWith($subject, 'livewire-pending-file:')) {
            return static::createFromLivewire(
                json_decode(Str::after($subject, 'livewire-pending-file:'), true)
            );
        } elseif (Str::startsWith($subject, 'livewire-pending-files:')) {
            $fileInfos = json_decode(Str::after($subject, 'livewire-pending-file:'), true);

            return collect($fileInfos)->map(function ($fileInfo) {
                return static::createFromLivewire($fileInfo);
            })->toArray();
        }
    }

    public function serializeForLivewireResponse()
    {
        return 'livewire-pending-file:'.json_encode([
            'name' => $this->name,
            'size' => $this->getSize(),
            'type' => $this->getMimeType(),
        ]);
    }

    public static function serializeMultipleForLivewireResponse($files)
    {
        return 'livewire-pending-files:'.json_encode(
            collect($files)->map(function ($file) {
                return [
                    'name' => $file->name,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            })->toArray()
        );
    }
}
