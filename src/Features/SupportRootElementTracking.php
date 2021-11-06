<?php

namespace Livewire\Features;

use Livewire\Livewire;

class SupportRootElementTracking
{
    static function init() { return new static; }

    function __construct()
    {
        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            if (! $html = data_get($response, 'effects.html')) return;

            data_set($response, 'effects.html', $this->addComponentEndingMarker($html, $component));
        });
    }

    public function addComponentEndingMarker($html, $component)
    {
        // The use of conditional comments https://en.wikipedia.org/wiki/Conditional_comment
        // is to prevent minification services like Cloudflare's Auto Minify from stripping the
        // comments (https://community.cloudflare.com/t/omit-formatted-comments-from-minification/18572/21)
        return $html."\n<!--[if false]><![endif] Livewire Component wire-end:".$component->id.' -->';
    }

    public static function stripOutEndingMarker($html)
    {
        return preg_replace('/<!--\[if false\]><!\[endif\] Livewire Component wire-end:.*? -->/', '', $html);
    }
}
