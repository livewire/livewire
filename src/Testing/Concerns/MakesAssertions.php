<?php

namespace Livewire\Testing\Concerns;

use PHPUnit\Framework\Assert as PHPUnit;

trait MakesAssertions
{
    public function assertDontSee($text)
    {
        return $this->assertSee($text, $negate = true);
    }

    public function assertSee($text, $negate = false)
    {
        return $this->assertSeeIn(null, $text, $negate);
    }

    public function assertDontSeeIn($selector, $text)
    {
        return $this->assertSeeIn($selector, $text, $negate = true);
    }

    public function assertSeeIn($selector, $text, $negate = false)
    {
        $source = $this->querySelector($selector)->text();

        $method = $negate ? 'assertNotContains' : 'assertContains';
        PHPUnit::{$method}((string) $text, strip_tags($source));

        return $this;
    }

    public function assertVisible($selector)
    {
        $nodes = $this->querySelector($selector);

        PHPUnit::assertGreaterThan(0, $nodes->count());

        return $this;
    }
}
