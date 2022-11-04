<?php

namespace Livewire\Features\SupportRedirects;

use PHPUnit\Framework\Assert as PHPUnit;

trait TestsRedirects
{
    public function assertRedirect($uri = null)
    {
        PHPUnit::assertArrayHasKey(
            'redirect',
            $this->effects,
            'Component did not perform a redirect.'
        );

        if (! is_null($uri)) {
            PHPUnit::assertSame(url($uri), url($this->effects['redirect']));
        }

        return $this;
    }

    public function assertNoRedirect()
    {
        PHPUnit::assertTrue(! isset($this->effects['redirect']));

        return $this;
    }
}
