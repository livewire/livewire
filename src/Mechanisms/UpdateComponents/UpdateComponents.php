<?php

namespace Livewire\Mechanisms\UpdateComponents;

use function Livewire\trigger;

use Livewire\Mechanisms\UpdateComponents\DehydrationContext;
use Livewire\Mechanisms\UpdateComponents\Checksum;
use Livewire\Mechanisms\RenderComponent;
use Livewire\Mechanisms\ComponentRegistry;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Drawer\Utils;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Contracts\Container\BindingResolutionException;
use Exception;
use Closure;

class UpdateComponents
{
    protected $synthesizers = [
        Synthesizers\LivewireSynth::class,
        Synthesizers\CarbonSynth::class,
        Synthesizers\CollectionSynth::class,
        Synthesizers\StringableSynth::class,
        Synthesizers\EnumSynth::class,
        Synthesizers\AnonymousSynth::class,
        Synthesizers\StdClassSynth::class,
        Synthesizers\ArraySynth::class,
    ];

    function boot()
    {
        app()->singleton($this::class);

        $this->skipRequestPayloadTamperingMiddleware();
    }

    function skipRequestPayloadTamperingMiddleware()
    {
        ConvertEmptyStringsToNull::skipWhen(function () {
            // @todo: update this...
            return request()->is('synthetic/update');
        });

        TrimStrings::skipWhen(function () {
            return request()->is('synthetic/update');
        });
    }

    function registerSynth($synthClass)
    {
        foreach ((array) $synthClass as $class) {
            array_unshift($this->synthesizers, $class);
        }
    }

    function mount($name, $params = [], $key = null)
    {
        $parent = app('livewire')->current();

        // Provide a way to interupt a mounting component and render entirely different html...
        try {
            $finishPreMount = trigger('pre-mount', $name, $params, $parent, $key, function ($html) {
                throw tap(new \Exception, fn ($e) => $e->__html = $html);
            });
        } catch (\Exception $e) {
            if ($e->__html) return [ $e->__html ];
            else throw $e;
        }

        $component = app('livewire')->new($name, $params);

        trigger('mount', $component, $params, $parent);

        $html = app(RenderComponent::class)::render($component) ?: '<div></div>';


        $payload = $this->snapshot($component, initial: true);

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            'wire:initial-data' => $payload,
        ]);

        $finishPreMount($component, $html);

        return [$html, $payload];
    }

    function update($snapshot, $diff, $calls)
    {
        $effects = [];

        Checksum::verify($snapshot);

        $component = $this->hydrate($data = $snapshot['data']);

        $this->updateProperties($component, $diff, $data);

        $this->makeCalls($component, $calls, $effects);

        $effects['html'] = app(\Livewire\Mechanisms\RenderComponent::class)::render($component);

        $payload = $this->snapshot($component, $effects);

        return [
            'target' => $component,
            'snapshot' => $payload['snapshot'],
            'effects' => $effects,
        ];
    }

    function snapshot($root, &$effects = [], $initial = false) {
        $finish = trigger('dehydrate.root', $root);

        $context = new DehydrationContext($root, $initial, []);

        $data = $this->dehydrate($root, $context, $effects);

        $finish($data, $effects);

        $snapshot = ['data' => $data];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        return ['snapshot' => $snapshot, 'effects' => $effects];
    }

    function dehydrate($target, &$context, &$effects) {
        if (Utils::isAPrimitive($target)) return $target;

        $synth = $this->synth($target);

        $initial = $context->initial;

        $methods = $synth->methods($target);

        if ($methods) $context->addEffect('methods', $methods);

        $value = $synth->dehydrate($target, $context, function ($childValue, $dataFromParent = []) use (&$effects, $initial, $target) {
            $childContext = new DehydrationContext($childValue, $initial, $dataFromParent);

            return $this->dehydrate($childValue, $childContext, $effects);
        });

        [$meta, $iEffects] = $context->retrieve();

        $meta['s'] = $synth::getKey();

        foreach ($iEffects as $key => $effect) {
            $effects[$key] = $effect;
        }

        return [$value, $meta];
    }

    function hydrate($data) {
        if (! Utils::isSyntheticTuple($data)) return $data;

        [$rawValue, $meta] = $data;
        $synthKey = $meta['s'];
        $synth = $this->synth($synthKey);

        return $synth->hydrate($rawValue, $meta, function ($childValue) {
            return $this->hydrate($childValue);
        });
    }

    function updateProperties($root, $diff, $data) {
        foreach ($diff as $path => $value) {
            $value = $this->hydrateForUpdate($data, $path, $value);

            $this->updateProperty($root, $path, $value);
        }
    }

    function hydrateForUpdate($raw, $path, $value)
    {
        $meta = $this->getMetaForPath($raw, $path);

        if ($meta) return $this->hydrate([$value, $meta]);

        return $value;
    }

    function getMetaForPath($raw, $path)
    {
        $segments = explode('.', $path);

        $first = array_shift($segments);

        [$data, $meta] = Utils::isSyntheticTuple($raw) ? $raw : [$raw, null];

        if ($path !== '') {
            $value = $data[$first] ?? null;

            return $this->getMetaForPath($value, implode('.', $segments));
        }

        return $meta;
    }

    function updateProperty(&$root, $path, $value)
    {
        $finish = trigger('update', $root, $path, $value);

        $segments = Utils::dotSegments($path);

        $this->recursivelySetValue($root, $root, $value, $segments);

        $finish($value);
    }

    function recursivelySetValue($root, $target, $leafValue, $segments, $index = 0)
    {
        $isLastSegment = count($segments) === $index + 1;

        $property = $segments[$index];

        $synth = $this->synth($target);

        assert($synth);

        if ($isLastSegment) {
            $toSet = $leafValue;
        } else {
            $propertyTarget = $synth->get($target, $property);

            // "$path" is a dot-notated key. This means we may need to drill
            // down and set a value on a deeply nested object. That object
            // may not exist, so let's find the first one that does...

            // Here's we've determined we're trying to set a deeply nested
            // value on an object/array that doesn't exist, so we need
            // to build up that non-existant nesting structure first.
            if ($propertyTarget === null) $propertyTarget = [];

            $toSet = $this->recursivelySetValue($root, $propertyTarget, $leafValue, $segments, $index + 1);
        }

        $method = ($leafValue === '__rm__' && $isLastSegment) ? 'unset' : 'set';

        $pathThusFar = collect($segments)->slice(0, $index + 1)->join('.');
        $fullPath = collect($segments)->join('.');

        $synth->$method($target, $property, $toSet, $pathThusFar, $fullPath, $root);

        return $target;
    }

    function makeCalls($root, $calls, &$effects) {
        foreach ($calls as $call) {
            $method = $call['method'];
            $params = $call['params'];
            $path = $call['path'];

            $target = $this->dataGet($root, $path);

            $addEffect = function ($key, $value) use (&$effects, $path) {
                $effects[$key] = $value;
            };

            $synth = $this->synth($target);

            if (! in_array($method, $synth->methods($target))) {
                throw new MethodNotFoundException($method);
            }

            $earlyReturnCalled = false;
            $earlyReturn = null;
            $returnEarly = function ($return = null) use (&$earlyReturnCalled, &$earlyReturn) {
                $earlyReturnCalled = true;
                $earlyReturn = $return;
            };

            $finish = trigger('call', $synth, $target, $method, $params, $addEffect, $returnEarly);

            $return = $earlyReturnCalled
                ? $earlyReturn
                : $this->synth($target)->call($target, $method, $params, $addEffect);

            $return = $finish($return);

            if (! isset($effects['returns'])) $effects['returns'] = [];
            if (! isset($effects['returns'][$path])) $effects['returns'][$path] = [];
            $effects['returns'][$path][] = $return;
        }
    }

    function dataGet($target, $key) {
        if (str($key)->exactly('')) return $target;

        if (! str($key)->contains('.')) {
            $thing = $this->synth($target)->get($target, $key);

            return $thing;
        }

        $parentKey = str($key)->before('.')->__toString();
        $childKey = str($key)->after('.')->__toString();

        $parent = $this->synth($target)->get($target, $parentKey);

        return $this->dataGet($parent, $childKey);
    }

    function synth($keyOrTarget) {
        return is_string($keyOrTarget)
            ? $this->getSynthesizerByKey($keyOrTarget)
            : $this->getSynthesizerByTarget($keyOrTarget);
    }

    function getSynthesizerByKey($key) {
        $forReturn = null;

        foreach ($this->synthesizers as $synth) {
            if ($synth::getKey() === $key) {
                $forReturn = new $synth;
                break;
            }
        }

        throw_unless($forReturn, new \Exception('No synthesizer found for key: "'.$key.'"'));

        return $forReturn;
    }

    function getSynthesizerByTarget($target) {
        $forReturn = null;

        foreach ($this->synthesizers as $synth) {
            if ($synth::match($target)) {
                $forReturn = new $synth;
                break;
            }
        }

        throw_unless($forReturn, new \Exception('Property type not supported in Livewire for property: ['.json_encode($target).']'));

        return $forReturn;
    }

    function getParentAndChildKey($path) {
        if (! str($path)->contains('.')) {
            return ['', $path];
        }

        $parentKey = str($path)->beforeLast('.')->__toString();
        $childKey = str($path)->afterLast('.')->__toString();

        return [$parentKey, $childKey];
    }
}


class AI {
    static function stringContainsDots() {}
}
