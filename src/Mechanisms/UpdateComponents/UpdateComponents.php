<?php

namespace Livewire\Mechanisms\UpdateComponents;

use function Livewire\trigger;
use function Livewire\wrap;

use Livewire\Mechanisms\UpdateComponents\ComponentContext;
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
use Livewire\Mechanisms\UpdateComponents\Synthesizers\Synth;

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

        $context = new ComponentContext($component, true);

        trigger('mount', $component, $params, $parent);

        $html = app(RenderComponent::class)::render($component) ?: '<div></div>';

        $payload = $this->snapshot($component, $context);

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            'wire:initial-data' => $payload,
        ]);

        $finishPreMount($component, $html);

        return [$html, $payload];
    }

    function update($snapshot, $diff, $calls)
    {
        Checksum::verify($snapshot);

        [$data, $meta] = $snapshot['data'];

        ['name' => $name, 'id' => $id] = $meta;

        $component = app('livewire')->new($name, [], $id);

        $context = new ComponentContext($component, false);

        foreach ($data as $key => $rawChild) {
            if (! property_exists($component, $key)) continue;

            $child = $this->hydrate($rawChild, $context, $key);

            // Typed properties shouldn't be set back to "null". It will throw an error...
            if ((new \ReflectionProperty($component, $key))->getType() && is_null($child)) continue;

            $component->$key = $child;
        }

        trigger('hydrate', $component, $meta, $context);

        $this->updateProperties($component, $diff, $data, $context);

        $this->makeCalls($component, $calls, $context);

        $context->addEffect('html', app(\Livewire\Mechanisms\RenderComponent::class)::render($component));

        $payload = $this->snapshot($component, $context);

        return [
            'target' => $component,
            'snapshot' => $payload['snapshot'],
            'effects' => $payload['effects'],
        ];
    }

    function snapshot($component, $context) {
        trigger('dehydrate', $component, $context);

        $data = Utils::getPublicPropertiesDefinedOnSubclass($component);

        foreach ($data as $key => $value) {
            $data[$key] = $this->dehydrate($value, $context, $key);
        }

        $metaTuple = [$data, [
            's' => 'lw',
            'id' => $component->getId(),
            'name' => $component->getName(),
            ...$context->meta,
        ]];

        $context->addEffect('methods', Utils::getPublicMethodsDefinedBySubClass($component));

        $snapshot = ['data' => $metaTuple];

        $checkum = Checksum::generate($snapshot);

        $snapshot['checksum'] = $checkum;

        return ['snapshot' => $snapshot, 'effects' => $context->effects];
    }

    function dehydrate($target, $context, $path) {
        if (Utils::isAPrimitive($target)) return $target;

        $synth = $this->synth($target, $context, $path);

        $methods = $synth->methods($target);

        if ($methods) $context->addEffect('methods', $methods);

        $metaTuple = $synth->dehydrate($target, function ($name, $childValue) use ($context, $path) {
            return $this->dehydrate($childValue, $context, "{$path}.{$name}");
        });

        [$data, $meta] = $metaTuple;

        $meta['s'] = $synth::getKey();

        return [$data, $meta];
    }

    function hydrate($data, $context, $path) {
        if (! Utils::isSyntheticTuple($data)) return $data;

        [$rawValue, $meta] = $data;
        $synthKey = $meta['s'];
        $synth = $this->synth($synthKey, $context, $path);

        return $synth->hydrate($rawValue, $meta, function ($name, $childValue) use ($context, $path) {
            return $this->hydrate($childValue, $context, "{$path}.{$name}");
        });
    }

    function updateProperties($root, $diff, $data, $context) {
        foreach ($diff as $path => $value) {
            $value = $this->hydrateForUpdate($data, $path, $value, $context);

            $this->updateProperty($root, $path, $value, $context);
        }
    }

    function hydrateForUpdate($raw, $path, $value, $context)
    {
        $meta = $this->getMetaForPath($raw, $path);

        if ($meta) return $this->hydrate([$value, $meta], $context, $path);

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

    function updateProperty(&$root, $path, $value, $context)
    {
        $finish = trigger('update', $root, $path, $value);

        $segments = Utils::dotSegments($path);

        $this->recursivelySetValue($root, $root, $value, $segments, 0, $context);

        $finish($value);
    }

    function recursivelySetValue($root, $target, $leafValue, $segments, $index = 0, $context = null)
    {
        $isLastSegment = count($segments) === $index + 1;

        $property = $segments[$index];

        $path = implode('.', array_slice($segments, 0, $index + 1));

        $synth = $this->synth($target, $context, $path);

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

            $toSet = $this->recursivelySetValue($root, $propertyTarget, $leafValue, $segments, $index + 1, $context);
        }

        $method = ($leafValue === '__rm__' && $isLastSegment) ? 'unset' : 'set';

        $pathThusFar = collect($segments)->slice(0, $index + 1)->join('.');
        $fullPath = collect($segments)->join('.');

        $synth->$method($target, $property, $toSet, $pathThusFar, $fullPath, $root);

        return $target;
    }

    function makeCalls($root, $calls, $context) {
        foreach ($calls as $call) {
            $method = $call['method'];
            $params = $call['params'];
            $path = $call['path'];

            $methods = Utils::getPublicMethodsDefinedBySubClass($root);

            // @todo: move this elsewhere...
            // Remove JS methods from method list:
            // $jsMethods = $this->getJsMethods($target);

            // Also remove "render" from the list...
            $methods =  array_values(array_diff($methods, ['render']));

            $addMethod = function ($name) use (&$methods) {
                array_push($methods, $name);
            };

            trigger('methods', $root, $addMethod);

            if (! in_array($method, $methods)) {
                throw new MethodNotFoundException($method);
            }

            $earlyReturnCalled = false;
            $earlyReturn = null;
            $returnEarly = function ($return = null) use (&$earlyReturnCalled, &$earlyReturn) {
                $earlyReturnCalled = true;
                $earlyReturn = $return;
            };

            $finish = trigger('call', $root, $method, $params, $context, $returnEarly);

            $return = $earlyReturnCalled
                ? $earlyReturn
                : wrap($root)->{$method}(...$params);

            $return = $finish($return);

            if (! isset($effects['returns'])) $effects['returns'] = [];
            if (! isset($effects['returns'][$path])) $effects['returns'][$path] = [];
            $effects['returns'][$path][] = $return;
        }
    }

    function dataGet($target, $key, $context) {
        if (str($key)->exactly('')) return $target;

        if (! str($key)->contains('.')) {
            $thing = $this->synth($target, $context)->get($target, $key);

            return $thing;
        }

        $parentKey = str($key)->before('.')->__toString();
        $childKey = str($key)->after('.')->__toString();

        $parent = $this->synth($target, $context)->get($target, $parentKey);

        return $this->dataGet($parent, $childKey, $context);
    }

    function synth($keyOrTarget, $context, $path): Synth {
        return is_string($keyOrTarget)
            ? $this->getSynthesizerByKey($keyOrTarget, $context, $path)
            : $this->getSynthesizerByTarget($keyOrTarget, $context, $path);
    }

    function getSynthesizerByKey($key, $context, $path) {
        foreach ($this->synthesizers as $synth) {
            if ($synth::getKey() === $key) {
                return new $synth($context, $path);
            }
        }

        throw new \Exception('No synthesizer found for key: "'.$key.'"');
    }

    function getSynthesizerByTarget($target, $context, $path): Synth {
        foreach ($this->synthesizers as $synth) {
            if ($synth::match($target)) {
                return new $synth($context, $path);
            }
        }

        throw new \Exception('Property type not supported in Livewire for property: ['.json_encode($target).']');
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
