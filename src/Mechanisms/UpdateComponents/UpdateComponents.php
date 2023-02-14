<?php

namespace Livewire\Mechanisms\UpdateComponents;

use function Livewire\trigger;

use Livewire\Mechanisms\UpdateComponents\DehydrationContext;
use Livewire\Mechanisms\UpdateComponents\Checksum;
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

        Route::post('/synthetic/update', function () {
            $targets = request('targets');

            $responses = [];

            foreach ($targets as $target) {
                $snapshot = $target['snapshot'];
                $diff = $target['diff'];
                $calls = $target['calls'];

                $response = app($this::class)->update($snapshot, $diff, $calls);

                unset($response['target']);

                $responses[] = $response;
            }

            return $responses;
        })->middleware('web')->name('synthetic.update');

        $this->skipRequestPayloadTamperingMiddleware();
    }

    function skipRequestPayloadTamperingMiddleware()
    {
        ConvertEmptyStringsToNull::skipWhen(function () {
            return request()->is('synthetic/update');
        });

        TrimStrings::skipWhen(function () {
            return request()->is('synthetic/update');
        });
    }

    public function registerSynth($synthClass)
    {
        foreach ((array) $synthClass as $class) {
            array_unshift($this->synthesizers, $class);
        }
    }

    protected $metasByPath = [];

    function update($snapshot, $diff, $calls)
    {
        $effects = [];

        $root = $this->fromSnapshot($snapshot, $diff);

        $finish = trigger('call.root', $root, $calls);

        $this->makeCalls($root, $calls, $effects);

        $finish();

        $payload = $this->toSnapshot($root, $effects);

        return [
            'target' => $root,
            'snapshot' => $payload['snapshot'],
            'effects' => $effects,
        ];
    }

    function toSnapshot($root, &$effects = [], $initial = false) {
        $finish = trigger('dehydrate.root', $root);

        $data = $this->dehydrate($root, $root, $effects, $initial);

        $finish($data, $effects);

        $this->metasByPath = [];

        $snapshot = ['data' => $data];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        return ['snapshot' => $snapshot, 'effects' => $effects];
    }

    function fromSnapshot($snapshot, $diff) {
        Checksum::verify($snapshot);

        $finish = trigger('hydrate.root', $snapshot);

        $root = $this->hydrate($snapshot['data']);

        trigger('boot', $root);

        $finish($root);

        $finish = trigger('update.root', $root);

        $this->updateProperties($root, $diff);

        $finish();

        return $root;
    }

    function dehydrate($root, $target, &$effects, $initial, $annotationsFromParent = [], $path = '') {
        if (Utils::isNotAPrimitive($target)) {
            $synth = $this->synth($target);

            $context = new DehydrationContext($root, $target, $initial, $annotationsFromParent, $path);

            $finish = trigger('dehydrate', $synth, $target, $context);

            $methods = $synth->methods($target);

            if ($methods) $context->addEffect('methods', $methods);

            $value = $synth->dehydrate($target, $context);

            $value = $finish($value);

            [$meta, $iEffects] = $context->retrieve();

            $meta['s'] = $synth::getKey();

            foreach ($iEffects as $key => $effect) {
                $effects[$key] = $effect;
            }

            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    $annotationsFromParent = $context->annotations[$key] ?? [];

                    $value[$key] = $this->dehydrate($root, $child, $effects, $initial, $annotationsFromParent, $path === '' ? $key : $path.'.'.$key);
                }
            }

            return [$value, $meta];
        } else {
            if (is_array($target)) {
                foreach ($target as $key => $child) {
                    $target[$key] = $this->dehydrate($child, $effects, $initial, $path === '' ? $key : $path.'.'.$key);
                }
            }
        }

        return $target;
    }

    function hydrate($data, $path = null) {
        if (Utils::isSyntheticTuple($data)) {
            [$rawValue, $meta] = $data;
            $synthKey = $meta['s'];
            $synth = $this->synth($synthKey);
            $this->metasByPath[$path] = $meta;

            if (is_array($rawValue)) {
                foreach ($rawValue as $key => $i) {
                    $rawValue[$key] = $this->hydrate($i, $path ? $path.'.'.$key : $key);
                }
            }

            $finish = trigger('hydrate', $synth, $rawValue, $meta);

            $return = $synth->hydrate($rawValue, $meta);

            return $finish($return);
        }

        return $data;
    }

    function updateProperties($root, $diff) {
        foreach ($diff as $path => $value) {
            $this->updateProperty($root, $path, $value);
        }
    }

    function updateProperty(&$root, $path, $value, $skipHydrate = false)
    {
        if (! $skipHydrate) {
            if (isset($this->metasByPath[$path])) {
                $value = [$value, $this->metasByPath[$path]];
            }

            $value = $this->hydrate($value, $path);
        }

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

    protected function makeCalls($root, $calls, &$effects) {
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

    protected function &dataGet(&$target, $key) {
        if (str($key)->exactly('')) return $target;

        if (! str($key)->contains('.')) {
            $thing =& $this->synth($target)->get($target, $key);

            return $thing;
        }

        $parentKey = str($key)->before('.')->__toString();
        $childKey = str($key)->after('.')->__toString();

        $parent =& $this->synth($target)->get($target, $parentKey);

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
