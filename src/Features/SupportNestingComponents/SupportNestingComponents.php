<?php

namespace Livewire\Features\SupportNestingComponents;

use function Livewire\store;
use function Livewire\on;
use function Livewire\trigger;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;

class SupportNestingComponents
{
    function boot()
    {
        $this->preserveChildTrackingWhenSkippingRenderOnParent();

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            return function () use ($target, $context) {
                $context->addMeta('children', $target->getChildren());
            };
        });

        on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($target) use ($meta) {
                $children = $meta['children'];

                $target->setPreviouslyRenderedChildren($children);
            };
        });

        on('mount', function ($name, $params, $parent, $key, $hijack) {
            // If this has already been rendered spoof it...
            if ($parent && $parent->hasPreviouslyRenderedChild($key)) {
                [$tag, $childId] = $parent->getPreviouslyRenderedChild($key);

                $finish = trigger('dummy-mount', $tag, $childId, $params, $parent, $key);

                $html  = "<{$tag} wire:id=\"{$childId}\"></{$tag}>";

                $parent->setChild($key, $tag, $childId);

                return $hijack($html);
            }

            return function ($component) use ($parent, $key) {
                return function ($html) use ($component, $parent, $key) {
                    if ($parent) {
                        preg_match('/<([a-zA-Z0-9\-]*)/', $html, $matches, PREG_OFFSET_CAPTURE);
                        $tag = $matches[1][0];
                        $parent->setChild($key, $tag, $component->getId());
                    }
                };
            };
        });
    }

    function preserveChildTrackingWhenSkippingRenderOnParent()
    {
        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $skipRender = store($target)->get('skipRender');

            if (! $skipRender) return;

            $target->keepRenderedChildren();
        });
    }
}
