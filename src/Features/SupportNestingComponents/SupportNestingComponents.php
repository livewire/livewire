<?php

namespace Livewire\Features\SupportNestingComponents;

use function Livewire\trigger;
use function Livewire\store;
use function Livewire\on;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Livewire\ComponentHook;

class SupportNestingComponents extends ComponentHook
{
    static function provide()
    {
        on('pre-mount', function ($name, $params, $parent, $key, $hijack) {
            // If this has already been rendered spoof it...
            if ($parent && static::hasPreviouslyRenderedChild($parent, $key)) {
                [$tag, $childId] = static::getPreviouslyRenderedChild($parent, $key);

                $finish = trigger('dummy-mount', $tag, $childId, $params, $parent, $key);

                $html  = "<{$tag} wire:id=\"{$childId}\"></{$tag}>";

                static::setParentChild($parent, $key, $tag, $childId);

                return $hijack($html);
            }

            return function ($component, $html) use ($parent, $key) {
                if ($parent) {
                    preg_match('/<([a-zA-Z0-9\-]*)/', $html, $matches, PREG_OFFSET_CAPTURE);
                    $tag = $matches[1][0];
                    static::setParentChild($parent, $key, $tag, $component->getId());
                }
            };
        });
    }

    function hydrate($meta)
    {
        $children = $meta['children'];

        $this->setPreviouslyRenderedChildren($this->component, $children);
    }

    function dehydrate($context)
    {
        $skipRender = $this->storeGet('skipRender');

        if ($skipRender) $this->keepRenderedChildren();

        return function () use ($context) {
            $context->addMeta('children', $this->getChildren());
        };
    }

    function getChildren() { return $this->storeGet('children', []); }
    function setChild($key, $tag, $id) { $this->storePush('children', [$tag, $id], $key); }

    static function setParentChild($parent, $key, $tag, $id) { store($parent)->push('children', [$tag, $id], $key); }
    static function setPreviouslyRenderedChildren($component, $children) { store($component)->set('previousChildren', $children); }
    static function hasPreviouslyRenderedChild($parent, $key) {
        return in_array($key, array_keys(store($parent)->get('previousChildren', [])));
    }

    static function getPreviouslyRenderedChild($parent, $key)
    {
        return store($parent)->get('previousChildren')[$key];
    }

    function keepRenderedChildren()
    {
        $this->storeSet('children', $this->storeGet('previousChildren'));
    }
}
