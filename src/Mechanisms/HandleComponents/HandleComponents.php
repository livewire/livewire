<?php

namespace Livewire\Mechanisms\HandleComponents;

use Exception;
use function Livewire\{ store, trigger, wrap };
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Livewire\Drawer\Utils;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class HandleComponents
{
    protected $propertySynthesizers = [
        Synthesizers\CarbonSynth::class,
        Synthesizers\CollectionSynth::class,
        Synthesizers\StringableSynth::class,
        Synthesizers\EnumSynth::class,
        Synthesizers\StdClassSynth::class,
        Synthesizers\ArraySynth::class,
    ];

    public static $renderStack = [];

    public function boot()
    {
        app()->singleton($this::class);
    }

    public function registerPropertySynthesizer($synth)
    {
        foreach ((array) $synth as $class) {
            array_unshift($this->propertySynthesizers, $class);
        }
    }

    public function mount($name, $params = [], $key = null)
    {
        $parent = app('livewire')->current();

        if ($html = $this->shortCircuitMount($name, $params, $key, $parent)) return $html;

        $component = app('livewire')->new($name, $params);

        $context = new ComponentContext($component, mounting: true);

        $finish = trigger('mount', $component, $params, $key, $parent);

        $html = $this->render($component, '<div></div>');

        trigger('dehydrate', $component, $context);

        $snapshot = $this->snapshot($component, $context);

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            'wire:snapshot' => $snapshot,
            'wire:effects' => $context->effects,
        ]);

        return $finish($html, $snapshot);
    }

    protected function shortCircuitMount($name, $params, $key, $parent)
    {
        $newHtml = null;

        trigger('pre-mount', $name, $params, $key, $parent, function ($html) use (&$newHtml) {
            $newHtml = $html;
        });

        return $newHtml;
    }

    public function update($snapshot, $updates, $calls)
    {
        $data = $snapshot['data'];
        $memo = $snapshot['memo'];

        [ $component, $context ] = $this->fromSnapshot($snapshot);

        trigger('hydrate', $component, $memo, $context);

        $this->updateProperties($component, $updates, $data, $context);

        $this->callMethods($component, $calls, $context);

        if ($html = $this->render($component)) {
            $context->addEffect('html', $html);
        }

        trigger('dehydrate', $component, $context);

        $snapshot = $this->snapshot($component, $context);

        return [ $snapshot, $context->effects ];
    }

    public function fromSnapshot($snapshot)
    {
        Checksum::verify($snapshot);

        $data = $snapshot['data'];
        $name = $snapshot['memo']['name'];
        $id   = $snapshot['memo']['id'];

        $component = app('livewire')->new($name, id: $id);

        $context = new ComponentContext($component);

        $this->hydrateProperties($component, $data, $context);

        return [ $component, $context ];
    }

    public function snapshot($component, $context = null)
    {
        $context ??= new ComponentContext($component);

        $data = $this->dehydrateProperties($component, $context);

        $snapshot = [
            'data' => $data,
            'memo' => [
                'id' => $component->getId(),
                'name' => $component->getName(),
                ...$context->memo,
            ],
        ];

        $snapshot['checksum'] = Checksum::generate($snapshot);

        return $snapshot;
    }

    protected function dehydrateProperties($component, $context)
    {
        $data = Utils::getPublicPropertiesDefinedOnSubclass($component);

        foreach ($data as $key => $value) {
            $data[$key] = $this->dehydrate($value, $context, $key);
        }

        return $data;
    }

    protected function dehydrate($target, $context, $path)
    {
        if (Utils::isAPrimitive($target)) return $target;

        $synth = $this->propertySynth($target, $context, $path);

        [ $data, $meta ] = $synth->dehydrate($target, function ($name, $child) use ($context, $path) {
            return $this->dehydrate($child, $context, "{$path}.{$name}");
        });

        $meta['s'] = $synth::getKey();

        return [ $data, $meta ];
    }

    protected function hydrateProperties($component, $data, $context)
    {
        foreach ($data as $key => $value) {
            if (! property_exists($component, $key)) continue;

            $child = $this->hydrate($value, $context, $key);

            // Typed properties shouldn't be set back to "null". It will throw an error...
            if ((new \ReflectionProperty($component, $key))->getType() && is_null($child)) continue;

            $component->$key = $child;
        }
    }

    protected function hydrate($valueOrTuple, $context, $path)
    {
        if (! Utils::isSyntheticTuple($value = $tuple = $valueOrTuple)) return $value;

        [$value, $meta] = $tuple;

        $synth = $this->propertySynth($meta['s'], $context, $path);

        return $synth->hydrate($value, $meta, function ($name, $child) use ($context, $path) {
            return $this->hydrate($child, $context, "{$path}.{$name}");
        });
    }

    protected function render($component, $default = null)
    {
        if ($html = store($component)->get('skipRender', false)) {
            $html = value(is_string($html) ? $html : $default);

            return Utils::insertAttributesIntoHtmlRoot($html, [
                'wire:id' => $component->getId(),
            ]);
        }

        [ $view, $properties ] = $this->getView($component);

        return $this->trackInRenderStack($component, function () use ($component, $view, $properties) {
            $finish = trigger('render', $component, $view, $properties);

            $revertA = Utils::shareWithViews('__livewire', $component);
            $revertB = Utils::shareWithViews('_instance', $component); // @deprecated

            $html = $view->render();

            $revertA(); $revertB();

            $html = Utils::insertAttributesIntoHtmlRoot($html, [
                'wire:id' => $component->getId(),
            ]);

            $finish($html);

            return $html;
        });
    }

    protected function getView($component)
    {
        $viewOrString = method_exists($component, 'render')
            ? wrap($component)->render()
            : view("livewire.{$component->getName()}");

        $properties = Utils::getPublicPropertiesDefinedOnSubclass($component);

        $view = Utils::generateBladeView($viewOrString, $properties);

        return [ $view, $properties ];
    }

    protected function trackInRenderStack($component, $callback)
    {
        array_push(static::$renderStack, $component);

        return tap($callback(), function () {
            array_pop(static::$renderStack);
        });
    }

    protected function updateProperties($component, $updates, $data, $context)
    {
        foreach ($updates as $path => $value) {
            $value = $this->hydrateForUpdate($data, $path, $value, $context);

            $this->updateProperty($component, $path, $value, $context);
        }
    }

    public function updateProperty($component, $path, $value, $context)
    {
        $segments = explode('.', $path);

        $property = array_shift($segments);

        $finish = trigger('update', $component, $path, $value);

        // If this isn't a "deep" set, set it directly, otherwise we have to
        // recursively get up and set down the value through the synths...
        if (empty($segments)) {
            if ($value !== '__rm__') $component->$property = $value;
        } else {
            $propertyValue = $component->$property;

            $component->$property = $this->recursivelySetValue($property, $propertyValue, $value, $segments, 0, $context);
        }

        $finish();
    }

    protected function hydrateForUpdate($raw, $path, $value, $context)
    {
        $meta = $this->getMetaForPath($raw, $path);

        if ($meta) return $this->hydrate([$value, $meta], $context, $path);

        return $value;
    }

    protected function getMetaForPath($raw, $path)
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

    protected function recursivelySetValue($baseProperty, $target, $leafValue, $segments, $index = 0, $context = null)
    {
        $isLastSegment = count($segments) === $index + 1;

        $property = $segments[$index];

        $path = implode('.', array_slice($segments, 0, $index + 1));

        $synth = $this->propertySynth($target, $context, $path);

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

            $toSet = $this->recursivelySetValue($baseProperty, $propertyTarget, $leafValue, $segments, $index + 1, $context);
        }

        $method = ($leafValue === '__rm__' && $isLastSegment) ? 'unset' : 'set';

        $pathThusFar = collect([$baseProperty, ...$segments])->slice(0, $index + 1)->join('.');
        $fullPath = collect([$baseProperty, ...$segments])->join('.');

        $synth->$method($target, $property, $toSet, $pathThusFar, $fullPath);

        return $target;
    }

    protected function callMethods($root, $calls, $context)
    {
        $returns = [];

        foreach ($calls as $call) {
            $method = $call['method'];
            $params = $call['params'];


            $earlyReturnCalled = false;
            $earlyReturn = null;
            $returnEarly = function ($return = null) use (&$earlyReturnCalled, &$earlyReturn) {
                $earlyReturnCalled = true;
                $earlyReturn = $return;
            };

            $finish = trigger('call', $root, $method, $params, $context, $returnEarly);

            if ($earlyReturnCalled) {
                $returns[] = $finish($earlyReturn);

                continue;
            }

            $methods = Utils::getPublicMethodsDefinedBySubClass($root);

            // Also remove "render" from the list...
            $methods =  array_values(array_diff($methods, ['render']));

            // @todo: put this in a better place:
            $methods[] = '__emit';

            if (! in_array($method, $methods)) {
                throw new MethodNotFoundException($method);
            }

            $return = wrap($root)->{$method}(...$params);

            $returns[] = $finish($return);
        }

        $context->addEffect('returns', $returns);
    }

    protected function propertySynth($keyOrTarget, $context, $path): Synth
    {
        return is_string($keyOrTarget)
            ? $this->getSynthesizerByKey($keyOrTarget, $context, $path)
            : $this->getSynthesizerByTarget($keyOrTarget, $context, $path);
    }

    protected function getSynthesizerByKey($key, $context, $path)
    {
        foreach ($this->propertySynthesizers as $synth) {
            if ($synth::getKey() === $key) {
                return new $synth($context, $path);
            }
        }

        throw new \Exception('No synthesizer found for key: "'.$key.'"');
    }

    protected function getSynthesizerByTarget($target, $context, $path)
    {
        foreach ($this->propertySynthesizers as $synth) {
            if ($synth::match($target)) {
                return new $synth($context, $path);
            }
        }

        throw new \Exception('Property type not supported in Livewire for property: ['.json_encode($target).']');
    }
}
