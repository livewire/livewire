<?php

namespace Livewire\Features\SupportNestingComponents;

use function Livewire\store;
use function Synthetic\on;
use Livewire\Mechanisms\DataStore;
use Livewire\LivewireSynth;

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

        on('mount', function ($name, $params, $parent, $key, $slots, $hijack) {
            // If this has already been rendered spoof it...
            if ($parent && $parent->hasPreviouslyRenderedChild($key)) {
                [$tag, $childId] = $parent->getPreviouslyRenderedChild($key);

                $finish = app('synthetic')->trigger('dummy-mount', $tag, $childId, $params, $parent, $key);

                $html  = "<{$tag} wire:id=\"{$childId}\"></{$tag}>";

                $parent->setChild($key, $tag, $childId);

                return $hijack($html);
            }
        });
    }

    function preserveChildTrackingWhenSkippingRenderOnParent()
    {
        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            $skipRender = store($target)->get('skipRender');

            if (! $skipRender) return;

            $target->keepRenderedChildren();
        });
    }
}
