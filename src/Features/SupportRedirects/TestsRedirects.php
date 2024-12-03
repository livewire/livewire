<?php

namespace Livewire\Features\SupportRedirects;

use Illuminate\Support\Str;
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

    public function assertRedirectContains($uri)
    {
        if (is_subclass_of($uri, Component::class)) {
            $uri = url()->action($uri);
        }

        if (! app('livewire')->isLivewireRequest()) {
            $this->lastState->getResponse()->assertRedirectContains($uri);

            return $this;
        }

        PHPUnit::assertArrayHasKey(
            'redirect',
            $this->effects,
            'Component did not perform a redirect.'
        );

        PHPUnit::assertTrue(
            Str::contains($this->effects['redirect'], $uri), 'Redirect location ['.$this->effects['redirect'].'] does not contain ['.$uri.'].'
        );

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
