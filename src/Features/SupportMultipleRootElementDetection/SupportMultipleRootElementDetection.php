<?php

namespace Livewire\Features\SupportMultipleRootElementDetection;

use Livewire\ComponentHook;

use function Livewire\on;

class SupportMultipleRootElementDetection extends ComponentHook
{
    static function provide() {
        on('mount', function ($component) {
            if (! config('app.debug')) return;

            return function ($html) use ($component) {
                (new static)->warnAgainstMoreThanOneRootElement($component, $html);
            };
        });
    }

    function warnAgainstMoreThanOneRootElement($component, $html)
    {
        $count = $this->getRootElementCount($html);

        if ($count > 1) {
            throw new MultipleRootElementsDetectedException($component);
        }
    }

    function getRootElementCount($html)
    {
        $dom = new \DOMDocument();

        @$dom->loadHTML($html);

        $body = $dom->getElementsByTagName('body')->item(0);

        $count = 0;

        foreach ($body->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                if ($child->tagName === 'script') continue;

                $count++;
            }
        }

        return $count;
    }
}
