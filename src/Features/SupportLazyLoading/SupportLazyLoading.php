<?php

namespace Livewire\Features\SupportLazyLoading;

use function Livewire\after;
use function Livewire\on;
use function Livewire\store;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\ComponentHook;

class SupportLazyLoading extends ComponentHook
{
    static function provide()
    {
        on('pre-mount', function ($name, $params, $parent, $key, $hijack) {
            if (! array_key_exists('lazy', $params)) return;
            unset($params['lazy']);

            // [$html] = app('livewire')->mount('__lazy', ['componentName' => $name, 'forwards' => $params], $key);
            [$html] = app(RenderComponent::class)->lazyMount($name, $params, $key);

            $hijack($html);
        });

        return;
        app('livewire')->component('__lazy', Lazy::class);

        on('pre-mount', function ($name, $params, $parent, $key, $hijack) {
            if ($name === '__lazy') return;
            if (! array_key_exists('lazy', $params)) return;
            dd($params);
            unset($params['lazy']);

            [$html] = app('livewire')->mount('__lazy', ['componentName' => $name, 'forwards' => $params], $key);

            $hijack($html);
        });

        on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;
            if (! $meta['name'] === '__lazy') return;

            return function ($target) {
                store($target)->set('lazyReadyForSwap', true);
                $target->swap = true;
            };
        });

        after('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;
            if (! store($target)->get('lazyReadyForSwap')) return;

            return function ($data) use ($context, $target) {
                $childContext = null;
                $childData = null;

                $off = on('dehydrate', function ($synth, $target, $context) use (&$childContext, &$childData) {
                    if (! $childContext) $childContext = $context;

                    return function ($data) use (&$childData) {
                        if (! $childData) $childData = $data;
                    };
                });

                [$html, $data] = app('livewire')->mount($target->componentName, [], id: $target->getId());

                $off();


                $context->effects = $childContext->effects;
                $context->meta = $childContext->meta;

                return $childData;
            };
        });
    }
}
