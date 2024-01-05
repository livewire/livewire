<?php

namespace Livewire\Features\SupportFileDownloads;

use PHPUnit\Framework\Assert as PHPUnit;

trait TestsFileDownloads
{
    public function assertFileDownloaded($filename = null, $content = null, $contentType = null)
    {
        $downloadEffect = data_get($this->effects, 'download');

        if ($filename) {
            PHPUnit::assertEquals(
                $filename,
                data_get($downloadEffect, 'name')
            );
        } else {
            PHPUnit::assertNotNull($downloadEffect);
        }

        if ($content) {
            $downloadedContent = data_get($this->effects, 'download.content');

            PHPUnit::assertEquals(
                $content,
                base64_decode($downloadedContent)
            );
        }

        if ($contentType) {
            PHPUnit::assertEquals(
                $contentType,
                data_get($this->effects, 'download.contentType')
            );
        }

        return $this;
    }

    public function assertNoFileDownloaded()
    {
        $downloadEffect = data_get($this->effects, 'download');

        PHPUnit::assertNull($downloadEffect);

        return $this;
    }
}
