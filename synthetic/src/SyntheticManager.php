<?php

namespace Synthetic;

use Synthetic\Synthesizers\StringableSynth;
use Synthetic\Synthesizers\CollectionSynth;
use Synthetic\Synthesizers\AnonymousSynth;
use Synthetic\Synthesizers\ObjectSynth;
use Synthetic\Synthesizers\CarbonSynth;
use Synthetic\Synthesizers\ArraySynth;
use Closure;
use Livewire\Drawer\Utils;

class SyntheticManager
{
    /**
     * Livewire & Synthetic rely heavily on events.
     * Here's a breakdown of what events get triggered when.
     *
     * When Synthesizing:
     * - "synthesize"
     * - "dehydrate.root"
     * - "dehydrate"
     *
     * When Updating:
     * - "hydrate.root"
     * - "hydrate"
     * - "update.root"
     * - "update"
     * - "call.root"
     * - "call"
     * - "dehydrate.root"
     * - "dehydrate"
     */

    use SyntheticValidation, SyntheticTesting;

    protected $synthesizers = [
        CarbonSynth::class,
        CollectionSynth::class,
        StringableSynth::class,
        AnonymousSynth::class,
        ObjectSynth::class,
        ArraySynth::class,
    ];

    public function registerSynth($synthClass)
    {
        foreach ((array) $synthClass as $class) {
            array_unshift($this->synthesizers, $class);
        }
    }

    protected $metasByPath = [];

    function new($name)
    {
        return $this->synthesize(new $name);
    }

    function synthesize($target)
    {
        $effects = [];

        $finish = $this->trigger('synthesize', $target);

        $result = $this->toSnapshot($target, $effects, $initial = true);

        $result = $finish($result);

        return $result;
    }

    function update($snapshot, $diff, $calls)
    {
        $effects = [];

        $root = $this->fromSnapshot($snapshot, $diff);

        $finish = $this->trigger('call.root', $root, $calls);

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
        $finish = $this->trigger('dehydrate.root', $root);

        $data = $this->dehydrate($root, $effects, $initial);

        $finish($data, $effects);

        $this->metasByPath = [];

        $snapshot = ['data' => $data];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        return ['snapshot' => $snapshot, 'effects' => $effects];
    }

    function fromSnapshot($snapshot, $diff) {
        Checksum::verify($snapshot);

        $finish = app('synthetic')->trigger('hydrate.root', $snapshot);

        $root = $this->hydrate($snapshot['data']);

        $finish($root);

        $finish = app('synthetic')->trigger('update.root', $root);

        $this->applyDiff($root, $diff);

        $finish();

        return $root;
    }

    function dehydrate($target, &$effects, $initial, $annotationsFromParent = [], $path = '') {
        $synth = $this->synth($target);

        if ($synth) {
            $context = new DehydrationContext($target, $initial, $annotationsFromParent);

            $finish = app('synthetic')->trigger('dehydrate', $synth, $target, $context);

            $methods = $synth->methods($target);

            if ($methods) $context->addEffect('methods', $methods);

            $value = $synth->dehydrate($target, $context);

            $value = $finish($value);

            [$meta, $iEffects] = $context->retrieve();

            $meta['s'] = $synth::getKey();

            foreach ($iEffects as $key => $effect) {
                if (! isset($effects[$path])) $effects[$path] = [];

                $effects[$path][$key] = $effect;
            }

            if (is_array($value)) {
                foreach ($value as $key => $child) {
                    $annotationsFromParent = $context->annotations[$key] ?? [];

                    $value[$key] = $this->dehydrate($child, $effects, $initial, $annotationsFromParent, $path === '' ? $key : $path.'.'.$key);
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

            $finish = app('synthetic')->trigger('hydrate', $synth, $rawValue, $meta);

            $return = $synth->hydrate($rawValue, $meta);

            return $finish($return);
        }

        return $data;
    }

    function applyDiff($root, $diff) {
        foreach ($diff as $path => $value) {
            // "$path" is a dot-notated key. This means we may need to drill
            // down and set a value on a deeply nested object. That object
            // may not exist, so let's find the first one that does...
            [$parentKey, $key] = $this->closestExistingParent($root, $path);

            $target =& $this->dataGet($root, $parentKey);

            if (isset($this->metasByPath[$path])) {
                $value = [$value, $this->metasByPath[$path]];
            }

            $value = $this->hydrate($value, $path);

            // "$leafValue" here is the most deeply nested value we're trying to set
            // on this "$root". We make this distinction, because "$value" may be
            // an array containing nesting levels that didn't previously exist.
            $leafValue = $value;

            if (Utils::containsDots($key)) {
                // Here's we've determined we're trying to set a deeply nested
                // value on an object/array that doesn't exist, so we need
                // to build up that non-existant nesting structure first.
                $nestedKey = Utils::afterFirstDot($key);

                $key = Utils::beforeFirstDot($key);

                $value = [];

                $value = data_set($value, $nestedKey, $leafValue);
            }

            $finish = $this->trigger('update', $root, $path, $leafValue);

            if ($value === '__rm__') {
                $this->synth($target)->unset($target, $key);
            } else {
                $this->synth($target)->set($target, $key, $value);
            }

            $finish($leafValue);
        }
    }

    protected function closestExistingParent(&$root, $path, $nestedKey = '')
    {
        if (! Utils::containsDots($path)) {
            return ['', $path];
        }

        [$parentKey, $key] = $this->getParentAndChildKey($path);

        $key = $nestedKey === '' ? $key : $key.'.'.$nestedKey;

        $parentTarget = $this->dataGet($root, $parentKey);

        if ($parentTarget !== null) {
            return [$parentKey, $key];
        };

        return $this->closestExistingParent($root, $parentKey, $key);
    }

    protected function makeCalls($root, $calls, &$effects) {
        $returns = [];

        foreach ($calls as $call) {
            $method = $call['method'];
            $params = $call['params'];
            $path = $call['path'];

            $target = $this->dataGet($root, $path);

            $addEffect = function ($key, $value) use (&$effects, $path) {
                if (! isset($effects[$path])) $effects[$path] = [];

                $effects[$path][$key] = $value;
            };

            $synth = $this->synth($target);

            if (! in_array($method, $synth->methods($target))) {
                throw new \Exception('Method call not allowed: ['.$method.']');
            }

            $finish = app('synthetic')->trigger('call', $synth, $target, $method, $params, $addEffect);

            $return = $this->synth($target)->call($target, $method, $params, $addEffect);

            $return = $finish($return);

            $return !== null && $addEffect('return', $return);
        }

        return $returns;
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
        foreach ($this->synthesizers as $synth) {
            if ($synth::getKey() === $key) {
                return new $synth;
            }
        }
    }

    function getSynthesizerByTarget($target) {
        foreach ($this->synthesizers as $synth) {
            if ($synth::match($target)) {
                return new $synth;
            }
        }
    }

    function getParentAndChildKey($path) {
        if (! str($path)->contains('.')) {
            return ['', $path];
        }

        $parentKey = str($path)->beforeLast('.')->__toString();
        $childKey = str($path)->afterLast('.')->__toString();

        return [$parentKey, $childKey];
    }

    protected $listeners = [];
    protected $listenersAfter = [];
    protected $listenersBefore = [];

    function trigger($name, &...$params) {
        $finishers = [];

        $listeners = array_merge(
            ($this->listenersBefore[$name] ?? []),
            ($this->listeners[$name] ?? []),
            ($this->listenersAfter[$name] ?? []),
        );

        foreach ($listeners as $callback) {
            $result = $callback(...$params);

            if ($result instanceof Closure) {
                $finishers[] = $result;
            }
        }

        return function (&$forward = null) use (&$finishers) {
            $latest = $forward;
            foreach ($finishers as $finisher) {
                $latest = $finisher($latest);
            }
            return $latest;
        };
    }

    function on($name, $callback) {
        if (! isset($this->listeners[$name])) $this->listeners[$name] = [];

        $this->listeners[$name][] = $callback;
    }

    function after($name, $callback) {
        if (! isset($this->listenersAfter[$name])) $this->listenersAfter[$name] = [];

        $this->listenersAfter[$name][] = $callback;
    }

    function before($name, $callback) {
        if (! isset($this->listenersBefore[$name])) $this->listenersBefore[$name] = [];

        $this->listenersBefore[$name][] = $callback;
    }

    function off($name, $callback) {
        $index = array_search($callback, $this->listeners[$name] ?? []);
        $indexAfter = array_search($callback, $this->listenersAfter[$name] ?? []);
        $indexBefore = array_search($callback, $this->listenersBefore[$name] ?? []);

        if ($index !== false) unset($this->listeners[$name][$index]);
        elseif ($indexAfter !== false) unset($this->listenersAfter[$name][$indexAfter]);
        elseif ($indexBefore !== false) unset($this->listenersBefore[$name][$indexBefore]);
    }
}
