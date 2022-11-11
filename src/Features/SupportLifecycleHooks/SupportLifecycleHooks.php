<?php

namespace Livewire\Features\SupportLifecycleHooks;

use function Livewire\after;
use function Livewire\wrap;

use function Livewire\trigger;
use function Livewire\of;
use function Livewire\on;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\Drawer\ImplicitlyBoundMethod;

class SupportLifecycleHooks
{
    function boot()
    {
        $this->preventLifecycleHooksFromBeingCalledDirectly();
        $this->handleBootHooks();
        $this->handleMountHooks();
        $this->handleHydrateHooks();
        $this->handleDehydrateHooks();
        $this->handleUpdateHooks();
    }

    function preventLifecycleHooksFromBeingCalledDirectly()
    {
        $protectedMethods = [
            'mount',
            'hydrate*',
            'dehydrate*',
            'updating*',
            'updated*',
        ];

        on('call', function ($synth, $target, $method, $params, $addEffect) use ($protectedMethods) {
            if (! $synth instanceof LivewireSynth) return;

            throw_if(
                str($method)->is($protectedMethods),
                new DirectlyCallingLifecycleHooksNotAllowedException($method, $target->getName())
            );
        });
    }

    function handleBootHooks()
    {
        // Cover the initial request, mounting, scenario...
        on('mount', function ($name, $params, $parent, $key, $hijack) {
            return function ($target) use ($params) {
                if (method_exists($target, 'boot')) wrap($target)->boot();

                trigger('component.boot', $target);
            };
        });

        after('mount', function ($name, $params) {
            return function ($target) use ($params) {
                if (method_exists($target, 'booted')) wrap($target)->booted();

                trigger('component.booted', $target);
            };
        });

        // Cover the subsequent request, hydration, scenario...
        on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($target) {
                if (method_exists($target, 'boot')) wrap($target)->boot();

                trigger('component.boot', $target);
            };
        });

        after('hydrate.root', function () {
            return function ($target) {
                if (! $target instanceof \Livewire\Component) return;

                if (method_exists($target, 'booted')) wrap($target)->booted();

                trigger('component.booted', $target);
            };
        });
    }

    function handleMountHooks()
    {
        // Note: "mount" is the only one of these events fired by Livewire...
        on('mount', function ($name, $params, $parent, $key, $hijack) {
            return function ($target) use ($params) {
                if (method_exists($target, 'mount')) {
                    wrap($target)->__call('mount', $params);
                }

                trigger('component.mount', $target, $params);
            };
        });
    }

    function handleHydrateHooks()
    {
        on('hydrate.root', function () {
            return function ($target) {
                if (! $target instanceof \Livewire\Component) return;

                // Call general "hydrate" hook...
                if (method_exists($target, 'hydrate')) wrap($target)->hydrate();

                // Call "hydrateXx" hooks for each property...
                foreach ($target->all() as $property => $value) {
                    $method = 'hydrate'.str($property)->studly();

                    if (method_exists($target, $method)) wrap($target)->$method($value);
                }

                trigger('component.hydrate', $target);
            };
        });
    }

    function handleDehydrateHooks()
    {
        on('dehydrate.root', function ($target) {
            if (! $target instanceof \Livewire\Component) return;

            // Call general "dehydrate" hook...
            if (method_exists($target, 'dehydrate')) wrap($target)->dehydrate();

            // Call "dehydrateXx" hooks for each property...
            foreach ($target->all() as $property => $value) {
                $method = 'dehydrate'.str($property)->studly();

                if (method_exists($target, $method)) wrap($target)->$method($value);
            }

            return function () use ($target) {
                trigger('component.dehydrate', $target);
            };
        });
    }

    function handleUpdateHooks()
    {
        on('update', function ($target, $path, $value) {
            if (! $target instanceof \Livewire\Component) return;

            $name = str($path);

            $propertyName = $name->studly()->before('.');
            $keyAfterFirstDot = $name->contains('.') ? $name->after('.')->__toString() : null;
            $keyAfterLastDot = $name->contains('.') ? $name->afterLast('.')->__toString() : null;

            $beforeMethod = 'updating'.$propertyName;
            $afterMethod = 'updated'.$propertyName;

            $beforeNestedMethod = $name->contains('.')
                ? 'updating'.$name->replace('.', '_')->studly()
                : false;

            $afterNestedMethod = $name->contains('.')
                ? 'updated'.$name->replace('.', '_')->studly()
                : false;

            $name = $name->__toString();

            if (method_exists($target, 'updating')) wrap($target)->updating($path, $value);

            if (method_exists($target, $beforeMethod)) {
                wrap($target)->{$beforeMethod}($value, $keyAfterFirstDot);
            }

            if ($beforeNestedMethod && method_exists($target, $beforeNestedMethod)) {
                wrap($target)->{$beforeNestedMethod}($value, $keyAfterLastDot);
            }

            trigger('component.updating', $target, $path, $value);

            return function ($newValue) use ($target, $path, $afterMethod, $afterNestedMethod, $keyAfterFirstDot, $keyAfterLastDot) {
                if (method_exists($target, 'updated')) wrap($target)->updated($path, $newValue);

                if (method_exists($target, $afterMethod)) {
                    wrap($target)->{$afterMethod}($newValue, $keyAfterFirstDot);
                }

                if ($afterNestedMethod && method_exists($target, $afterNestedMethod)) {
                    wrap($target)->{$afterNestedMethod}($newValue, $keyAfterLastDot);
                }

                trigger('component.updated', $target, $path, $newValue);
            };
        });
    }
}
