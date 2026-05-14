<?php

namespace Livewire\Mechanisms\HandleComponents;

use function Livewire\{on, store, trigger, wrap };
use Livewire\Mechanisms\Mechanism;
use Livewire\Mechanisms\HandleSynths\HandleSynths;
use Livewire\Exceptions\PublicPropertyNotFoundException;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Exceptions\MaxNestingDepthExceededException;
use Livewire\Exceptions\TooManyCallsException;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportFormObjects\Form;
use Illuminate\Support\Facades\View;

class HandleComponents extends Mechanism
{
    public static $renderStack = [];
    public static $componentStack = [];

    public function boot()
    {
        on('flush-state', function () {
            static::$renderStack = [];
            static::$componentStack = [];
            Utils::flushReflectionCache();
        });
    }

    public function mount($name, $params = [], $key = null, $slots = [])
    {
        $parent = app('livewire')->current();

        $component = app('livewire')->new($name);

        // Separate params into component properties and HTML attributes...
        [$componentParams, $htmlAttributes] = $this->separateParamsAndAttributes($component, $params);

        if ($html = $this->shortCircuitMount($name, $componentParams, $key, $parent, $slots, $htmlAttributes)) return $html;

        if (! empty($slots)) {
            $component->withSlots($slots, $parent);
        }

        if (! empty($htmlAttributes)) {
            $component->withHtmlAttributes($htmlAttributes);
        }

        $this->pushOntoComponentStack($component);

        try {
            $context = new ComponentContext($component, mounting: true);

            if (config('app.debug')) $start = microtime(true);
            $finish = trigger('mount', $component, $componentParams, $key, $parent, $htmlAttributes);
            if (config('app.debug')) trigger('profile', 'mount', $component->getId(), [$start, microtime(true)]);

            if (config('app.debug')) $start = microtime(true);
            $html = $this->render($component, '<div></div>');
            if (config('app.debug')) trigger('profile', 'render', $component->getId(), [$start, microtime(true)]);

            if (config('app.debug')) $start = microtime(true);
            trigger('dehydrate', $component, $context);

            $snapshot = $this->snapshot($component, $context);
            if (config('app.debug')) trigger('profile', 'dehydrate', $component->getId(), [$start, microtime(true)]);

            trigger('destroy', $component, $context);

            $html = Utils::insertAttributesIntoHtmlRoot($html, [
                'wire:snapshot' => $snapshot,
                'wire:effects' => $context->effects,
            ]);

            return $finish($html, $snapshot);
        } finally {
            $this->popOffComponentStack();
        }
    }

    protected function separateParamsAndAttributes($component, $params)
    {
        $componentParams = [];
        $htmlAttributes = [];

        // Get component's properties and mount method parameters
        $componentProperties = Utils::getPublicPropertiesDefinedOnSubclass($component);
        $mountParams = $this->getMountMethodParameters($component);

        foreach ($params as $key => $value) {
            $processedKey = $key;

            // Convert only kebab-case params to camelCase for matching...
            if (str($processedKey)->contains('-')) {
                $processedKey = str($processedKey)->camel()->toString();
            }

            // Check if this is a reserved param
            if ($this->isReservedParam($key)) {
                $componentParams[$key] = $value;
            }

            // Check if this maps to a component property or mount param
            elseif (
                array_key_exists($processedKey, $componentProperties)
                || in_array($processedKey, $mountParams)
                || is_numeric($key) // if the key is numeric, it's likely a mount parameter...
            ) {
                $componentParams[$processedKey] = $value;
            } else {
                // Keep as HTML attribute (preserve kebab-case)
                $htmlAttributes[$key] = $value;
            }
        }

        return [$componentParams, $htmlAttributes];
    }

    protected function isReservedParam($key)
    {
        $exact = ['lazy', 'defer', 'lazy.bundle', 'defer.bundle', 'wire:ref'];
        $startsWith = ['@'];

        // Check exact matches
        if (in_array($key, $exact)) {
            return true;
        }

        // Check starts_with patterns
        foreach ($startsWith as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }

    protected function getMountMethodParameters($component)
    {
        $parameters = [];

        // Get parameters from the component's own mount() method...
        if (method_exists($component, 'mount')) {
            $reflection = new \ReflectionMethod($component, 'mount');

            foreach ($reflection->getParameters() as $parameter) {
                $parameters[] = $parameter->getName();
            }
        }

        // Get parameters from trait mount hooks (e.g., mountMyTrait)...
        foreach (class_uses_recursive($component) as $trait) {
            $method = 'mount' . class_basename($trait);

            if (method_exists($component, $method)) {
                $reflection = new \ReflectionMethod($component, $method);

                foreach ($reflection->getParameters() as $parameter) {
                    $parameters[] = $parameter->getName();
                }
            }
        }

        return array_unique($parameters);
    }

    protected function shortCircuitMount($name, $params, $key, $parent, $slots, $htmlAttributes)
    {
        $newHtml = null;

        trigger('pre-mount', $name, $params, $key, $parent, function ($html) use (&$newHtml) {
            $newHtml = $html;
        }, $slots, $htmlAttributes);

        return $newHtml;
    }

    public function update($snapshot, $updates, $calls)
    {
        if (! is_array($snapshot)
            || ! is_array($snapshot['data'] ?? null)
            || ! is_array($snapshot['memo'] ?? null)
            || ! is_string($snapshot['checksum'] ?? null)
            || ! is_string($snapshot['memo']['id'] ?? null)
            || ! is_string($snapshot['memo']['name'] ?? null)
        ) {
            if (config('app.debug')) throw new \InvalidArgumentException('Invalid Livewire snapshot structure: expected [data], [memo], [checksum], [memo.id], and [memo.name].');

            abort(404);
        }

        foreach ($calls as $call) {
            if (! is_array($call)
                || ! is_string($call['method'] ?? null)
                || ! is_array($call['params'] ?? null)
            ) {
                if (config('app.debug')) throw new \InvalidArgumentException('Invalid Livewire call structure: each call must contain [method] (string) and [params] (array).');

                abort(404);
            }
        }

        $data = $snapshot['data'];
        $memo = $snapshot['memo'];

        if (config('app.debug')) $start = microtime(true);
        [ $component, $context ] = $this->fromSnapshot($snapshot);

        $this->pushOntoComponentStack($component);

        try {
            trigger('hydrate', $component, $memo, $context);

            $this->updateProperties($component, $updates, $data, $context);
            if (config('app.debug')) trigger('profile', 'hydrate', $component->getId(), [$start, microtime(true)]);

            $this->callMethods($component, $calls, $context);

            if (config('app.debug')) $start = microtime(true);
            if ($html = $this->render($component)) {
                $context->addEffect('html', $html);
                if (config('app.debug')) trigger('profile', 'render', $component->getId(), [$start, microtime(true)]);
            }

            if (config('app.debug')) $start = microtime(true);
            trigger('dehydrate', $component, $context);

            $snapshot = $this->snapshot($component, $context);
            if (config('app.debug')) trigger('profile', 'dehydrate', $component->getId(), [$start, microtime(true)]);

            trigger('destroy', $component, $context);

            return [ $snapshot, $context->effects ];
        } finally {
            $this->popOffComponentStack();
        }
    }

    public function fromSnapshot($snapshot)
    {
        Checksum::verify($snapshot);

        trigger('snapshot-verified', $snapshot);

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
            $data[$key] = app(HandleSynths::class)->dehydrate($value, $context, $key);
        }

        return $data;
    }

    protected function hydrateProperties($component, $data, $context)
    {
        foreach ($data as $key => $value) {
            if (! property_exists($component, $key)) continue;

            $child = app(HandleSynths::class)->hydrate($value, $context, $key);

            // Typed properties shouldn't be set back to "null". It will throw an error...
            if ((new \ReflectionProperty($component, $key))->getType() && is_null($child)) continue;

            $component->$key = $child;
        }
    }

    protected function render($component, $default = null)
    {
        if ($html = store($component)->get('skipRender', false)) {
            $html = value(is_string($html) ? $html : $default);

            if (! $html) return;

            return Utils::insertAttributesIntoHtmlRoot($html, [
                'wire:id' => $component->getId(),
                'wire:name' => $component->getName(),
            ]);
        }

        [ $view, $properties ] = $this->getView($component);

        return $this->trackInRenderStack($component, function () use ($component, $view, $properties) {
            $finish = trigger('render', $component, $view, $properties);

            $revertA = Utils::shareWithViews('__livewire', $component);
            $revertB = Utils::shareWithViews('_instance', $component); // @deprecated

            $viewContext = new ViewContext;

            $html = $view->render(function ($view) use ($viewContext) {
                // Extract leftover slots, sections, and pushes before they get flushed...
                $viewContext->extractFromEnvironment($view->getFactory());
            });

            $revertA(); $revertB();

            $html = Utils::insertAttributesIntoHtmlRoot($html, [
                'wire:id' => $component->getId(),
                'wire:name' => $component->getName(),
            ]);

            $replaceHtml = function ($newHtml) use (&$html) {
                $html = $newHtml;
            };

            $html = $finish($html, $replaceHtml, $viewContext);

            return $html;
        });
    }

    protected function getView($component)
    {
        $viewPath = config('livewire.view_path', resource_path('views/livewire'));

        $dotName = $component->getName();

        $fileName = str($dotName)->replace('.', '/')->__toString();

       $viewOrString = null;

        if (method_exists($component, 'render')) {
            $viewOrString = wrap($component)->render();
        } elseif ($component->hasProvidedView()) {
            $viewOrString = $component->getProvidedView();
        } else {
            $viewOrString = View::file($viewPath . '/' . $fileName . '.blade.php');
        }

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
        // When the JS diff algorithm detects that all properties of a form object
        // have changed, it consolidates them into a single update (e.g. {form: {title: '...', status: '...'}}).
        // Form objects are special — they should never be fully replaced. Instead, decompose
        // the consolidated update into individual property updates so each goes through
        // the normal hydration path (which handles type casting for enums, etc.)...
        $updates = $this->expandConsolidatedFormObjectUpdates($component, $updates);

        $finishes = [];

        foreach ($updates as $path => $value) {
            $value = app(HandleSynths::class)->hydrateForUpdate($data, $path, $value, $context);

            // We only want to run "updated" hooks after all properties have
            // been updated so that each individual hook has the ability
            // to overwrite the updated states of other properties...
            $finishes[] = $this->updateProperty($component, $path, $value, $context);
        }

        foreach ($finishes as $finish) {
            $finish();
        }
    }

    protected function expandConsolidatedFormObjectUpdates($component, $updates)
    {
        $expanded = [];

        foreach ($updates as $path => $value) {
            if (is_array($value) && property_exists($component, $path) && $component->$path instanceof Form) {
                foreach ($value as $key => $child) {
                    $expanded["{$path}.{$key}"] = $child;
                }
            } else {
                $expanded[$path] = $value;
            }
        }

        return $expanded;
    }

    public function updateProperty($component, $path, $value, $context)
    {
        $segments = explode('.', $path);

        $maxDepth = config('livewire.payload.max_nesting_depth');
        if ($maxDepth !== null && count($segments) > $maxDepth) {
            throw new MaxNestingDepthExceededException($path, $maxDepth);
        }

        $property = array_shift($segments);

        $finish = trigger('update', $component, $path, $value);

        // Ensure that it's a public property, not on the base class first...
        if (! in_array($property, array_keys(Utils::getPublicPropertiesDefinedOnSubclass($component)))) {
            throw new PublicPropertyNotFoundException($property, $component->getName());
        }

        // If this isn't a "deep" set, set it directly, otherwise we have to
        // recursively get up and set down the value through the synths...
        if (empty($segments)) {
            $this->setComponentPropertyAwareOfTypes($component, $property, $value);
        } else {
            $propertyValue = $component->$property;

            $this->setComponentPropertyAwareOfTypes($component, $property,
                $this->recursivelySetValue($property, $propertyValue, $value, $segments, 0, $context)
            );
        }

        return $finish;
    }

    protected function recursivelySetValue($baseProperty, $target, $leafValue, $segments, $index = 0, $context = null)
    {
        $isLastSegment = count($segments) === $index + 1;

        $property = $segments[$index];

        $path = implode('.', array_slice($segments, 0, $index + 1));

        $synths = app(HandleSynths::class);

        $synth = $synths->resolve($target, $context, $path);

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

        $method = ($synths->isRemoval($leafValue) && $isLastSegment) ? 'unset' : 'set';

        $pathThusFar = collect([$baseProperty, ...$segments])->slice(0, $index + 1)->join('.');
        $fullPath = collect([$baseProperty, ...$segments])->join('.');

        $synth->$method($target, $property, $toSet, $pathThusFar, $fullPath);

        return $target;
    }

    protected function setComponentPropertyAwareOfTypes($component, $property, $value)
    {
        try {
           $component->$property = $value;
        } catch (\TypeError $e) {
            // If an "int" is being set to empty string, unset the property (making it null).
            // This is common in the case of `wire:model`ing an int to a text field...
            // If a value is being set to "null", do the same...
            if ($value === '' || $value === null) {
                unset($component->$property);

                return;
            }

            // This is almost certainly a bot/scanner probing typed properties
            // with wrong-type values. Abort with 419 directly so it never
            // reaches the top-level catch (which would report it as a real bug).
            if (config('app.debug')) throw $e;

            abort(419);
        }
    }

    protected function callMethods($root, $calls, $componentContext)
    {
        $maxCalls = config('livewire.payload.max_calls');

        if ($maxCalls !== null && count($calls) > $maxCalls) {
            throw new TooManyCallsException(count($calls), $maxCalls);
        }

        $returns = [];

        foreach ($calls as $idx => $call) {
            $method = $call['method'];
            $params = $call['params'];
            $metadata = $call['metadata'] ?? [];

            $earlyReturnCalled = false;
            $earlyReturn = null;
            $returnEarly = function ($return = null) use (&$earlyReturnCalled, &$earlyReturn) {
                $earlyReturnCalled = true;
                $earlyReturn = $return;
            };

            $finish = trigger('call', $root, $method, $params, $componentContext, $returnEarly, $metadata, $idx);

            if ($earlyReturnCalled) {
                $returns[] = $finish($earlyReturn);

                continue;
            }

            $methods = Utils::getPublicMethodsDefinedBySubClass($root);

            // Also remove "render" from the list...
            $methods =  array_values(array_diff($methods, ['render']));

            // @todo: put this in a better place:
            $methods[] = '__dispatch';

            if (! in_array($method, $methods)) {
                throw new MethodNotFoundException($method);
            }

            if (config('app.debug')) $start = microtime(true);
            $return = wrap($root)->{$method}(...$params);
            if (config('app.debug')) trigger('profile', 'call'.$idx, $root->getId(), [$start, microtime(true)]);

            $returns[] = $finish($return);

            // Support `Wire:click.renderless`...
            if ($metadata['renderless'] ?? false) {
                $root->skipRender();
            }
        }

        $componentContext->addEffect('returns', $returns);
    }

    protected function pushOntoComponentStack($component)
    {
        array_push($this::$componentStack, $component);
    }

    protected function popOffComponentStack()
    {
        array_pop($this::$componentStack);
    }
}
