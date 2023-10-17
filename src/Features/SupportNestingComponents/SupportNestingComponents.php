<?php

namespace Livewire\Features\SupportNestingComponents;

use Livewire\ComponentHook;
use Livewire\Drawer\Utils;

use function Livewire\on;
use function Livewire\store;
use function Livewire\trigger;

class SupportNestingComponents extends ComponentHook
{
    public static function provide()
    {
        on('pre-mount', function ($name, $params, $key, $parent, $hijack) {
            // If this has already been rendered spoof it...
            if ($parent && static::hasPreviouslyRenderedChild($parent, $key)) {
                [$tag, $childId] = static::getPreviouslyRenderedChild($parent, $key);

                $finish = trigger('mount.stub', $tag, $childId, $params, $parent, $key);

                $html = "<{$tag} wire:id=\"{$childId}\"></{$tag}>";

                static::setParentChild($parent, $key, $tag, $childId);

                $hijack($finish($html));
            }
        });

        on('mount', function ($component, $params, $key, $parent) {
            $start = null;
            if ($parent && config('app.debug')) {
                $start = microtime(true);
            }

            static::setParametersToMatchingProperties($component, $params);

            return function ($html) use ($component, $key, $parent, $start) {
                if ($parent) {
                    if (config('app.debug')) {
                        trigger('profile', 'child:'.$component->getId(), $parent->getId(), [$start, microtime(true)]);
                    }

                    preg_match('/<([a-zA-Z0-9\-]*)/', $html, $matches, PREG_OFFSET_CAPTURE);
                    $tag = $matches[1][0];
                    static::setParentChild($parent, $key, $tag, $component->getId());
                }
            };
        });
    }

    public function hydrate($memo)
    {
        $children = $memo['children'];

        static::setPreviouslyRenderedChildren($this->component, $children);
    }

    public function dehydrate($context)
    {
        $skipRender = $this->storeGet('skipRender');

        if ($skipRender) {
            $this->keepRenderedChildren();
        }

        $context->addMemo('children', $this->getChildren());
    }

    public function getChildren()
    {
        return $this->storeGet('children', []);
    }

    public function setChild($key, $tag, $id)
    {
        $this->storePush('children', [$tag, $id], $key);
    }

    public static function setParentChild($parent, $key, $tag, $id)
    {
        store($parent)->push('children', [$tag, $id], $key);
    }

    public static function setPreviouslyRenderedChildren($component, $children)
    {
        store($component)->set('previousChildren', $children);
    }

    public static function hasPreviouslyRenderedChild($parent, $key)
    {
        return array_key_exists($key, store($parent)->get('previousChildren', []));
    }

    public static function getPreviouslyRenderedChild($parent, $key)
    {
        return store($parent)->get('previousChildren')[$key];
    }

    public function keepRenderedChildren()
    {
        $this->storeSet('children', $this->storeGet('previousChildren'));
    }

    public static function setParametersToMatchingProperties($component, $params)
    {
        // Assign all public component properties that have matching parameters.
        collect(array_intersect_key($params, Utils::getPublicPropertiesDefinedOnSubclass($component)))
            ->each(function ($value, $property) use ($component) {
                $component->{$property} = $value;
            });
    }
}
