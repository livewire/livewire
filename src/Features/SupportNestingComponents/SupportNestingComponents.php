<?php

namespace Livewire\Features\SupportNestingComponents;

use function Livewire\trigger;
use function Livewire\store;
use function Livewire\on;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

class SupportNestingComponents extends ComponentHook
{
    static function provide()
    {
        on('pre-mount', function ($name, $params, $key, $parent, $hijack, $slots, $attributes) {
            // If this has already been rendered spoof it...
            if ($parent && static::hasPreviouslyRenderedChild($parent, $key)) {
                [$tag, $childId] = static::getPreviouslyRenderedChild($parent, $key);

                $finish = trigger('mount.stub', $tag, $childId, $params, $parent, $key, $slots, $attributes);

                $idAttribute = " wire:id=\"{$childId}\"";
                $nameAttribute = " wire:name=\"{$name}\"";
                $keyAttribute = $key !== null ? " wire:key=\"{$key}\"" : '';

                $html = "<{$tag}{$idAttribute}{$nameAttribute}{$keyAttribute}></{$tag}>";

                static::setParentChild($parent, $key, $tag, $childId);

                $hijack($finish($html));
            }
        });

        on('mount', function ($component, $params, $key, $parent) {
            $start = null;
            if ($parent && config('app.debug')) $start = microtime(true);

            static::setParametersToMatchingProperties($component, $params);

            return function (&$html) use ($component, $key, $parent, $start) {
                if ($key !== null) {
                   $html = Utils::insertAttributesIntoHtmlRoot($html, [
                        'wire:key' => $key,
                    ]);
                }

                if ($parent) {
                    if (config('app.debug')) trigger('profile', 'child:'.$component->getId(), $parent->getId(), [$start, microtime(true)]);

                    preg_match('/<([a-zA-Z0-9\-]*)/', $html, $matches, PREG_OFFSET_CAPTURE);

                    $tag = $matches[1][0];

                    static::setParentChild($parent, $key, $tag, $component->getId());
                }
            };
        });
    }

    function hydrate($memo)
    {
        $children = $memo['children'];

        static::setPreviouslyRenderedChildren($this->component, $children);

        $this->ifThisComponentIsAChildThatHasBeenRemovedByTheParent(function () {
            // Let's skip its render so that we aren't wasting extra rendering time
            // on a component that has already been thrown-away by its parent...
            $this->component->skipRender();
        });
    }

    function dehydrate($context)
    {
        $skipRender = $this->storeGet('skipRender');

        if ($skipRender) $this->keepRenderedChildren();

        $this->storeRemovedChildrenToReferenceWhenThoseChildrenHydrateSoWeCanSkipTheirRenderAndAvoideUselessWork();

        $context->addMemo('children', $this->getChildren());
    }

    function getChildren() { return $this->storeGet('children', []); }
    function setChild($key, $tag, $id) { $this->storePush('children', [$tag, $id], $key); }

    static function setParentChild($parent, $key, $tag, $id) { store($parent)->push('children', [$tag, $id], $key); }
    static function setPreviouslyRenderedChildren($component, $children) { store($component)->set('previousChildren', $children); }
    static function hasPreviouslyRenderedChild($parent, $key) {
        return array_key_exists($key, store($parent)->get('previousChildren', []));
    }

    static function getPreviouslyRenderedChild($parent, $key)
    {
        $child = store($parent)->get('previousChildren')[$key];

        [$tag, $childId] = $child;

        // Validate tag name - only allow valid HTML tag characters (letters, numbers, hyphens)
        // Must start with a letter to be a valid HTML tag
        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9\-]*$/', $tag)) {
            throw new \Exception('Invalid Livewire child tag name. Tag names must only contain letters, numbers, and hyphens.');
        }

        // Validate child ID format - only allow alphanumeric and hyphens
        if (! preg_match('/^[a-zA-Z0-9\-]+$/', $childId)) {
            throw new \Exception('Invalid Livewire child component ID format.');
        }

        return $child;
    }

    function keepRenderedChildren()
    {
        $this->storeSet('children', $this->storeGet('previousChildren'));
    }

    static function setParametersToMatchingProperties($component, $params)
    {
        // Assign all public component properties that have matching parameters.
        collect(array_intersect_key($params, Utils::getPublicPropertiesDefinedOnSubclass($component)))
            ->each(function ($value, $property) use ($component) {
                $component->{$property} = $value;
            });
    }

    protected function storeRemovedChildrenToReferenceWhenThoseChildrenHydrateSoWeCanSkipTheirRenderAndAvoideUselessWork()
    {
        // Get a list of children that we're "removed" in this request...
        $removedChildren = array_diff_key($this->storeGet('previousChildren', []), $this->getChildren());

        foreach ($removedChildren as $key => $child) {
            store()->push('removedChildren', $key, $child[1]);
        }
    }

    protected function ifThisComponentIsAChildThatHasBeenRemovedByTheParent($callback)
    {
        $removedChildren = store()->get('removedChildren', []);

        if (isset($removedChildren[$this->component->getId()])) {
            $callback();

            store()->unset('removedChildren', $this->component->getId());
        }
    }
}
