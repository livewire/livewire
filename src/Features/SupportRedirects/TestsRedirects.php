<?php

namespace Livewire\Features\SupportRedirects;

use Livewire\Component;
use PHPUnit\Framework\Assert as PHPUnit;

trait TestsRedirects
{
    public function assertRedirect($uri = null)
    {
        if (is_subclass_of($uri, Component::class)) {
            $uri = url()->action($uri);
        }

        if (! app('livewire')->isLivewireRequest()) {
            $this->lastState->getResponse()->assertRedirect($uri);

            return $this;
        }

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

    public function assertRedirectToRoute($name, $parameters = [])
    {
        $uri = route($name, $parameters);

        return $this->assertRedirect($uri);
    }

    public function assertNoRedirect()
    {
        PHPUnit::assertTrue(! isset($this->effects['redirect']));

        return $this;
    }
}
