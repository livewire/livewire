<?php

namespace Livewire\RenameMe;

use Livewire\Livewire;

class OptimizeRenderedDom
{
    static function init() { return new static; }

    protected $htmlHashesByComponent = [];

    function __construct()
    {
        Livewire::listen('component.dehydrate.initial', function ($component, $response) {
            $response->memo['htmlHash'] = hash('crc32b', $response->effects['html']);
        });

        Livewire::listen('component.hydrate.subsequent', function ($component, $request) {
            $this->htmlHashesByComponent[$component->id] = $request->memo['htmlHash'];
        });

        Livewire::listen('component.dehydrate.subsequent', function ($component, $response) {
            $oldHash = $this->htmlHashesByComponent[$component->id] ?? null;

            $response->memo['htmlHash'] = $newHash = hash('crc32b', $response->effects['html']);

            if ($oldHash === $newHash) {
                $response->effects['html'] = null;
            }
        });
    }
}
