<?php

namespace Livewire\Features\SupportNestedComponentListeners;

use function Livewire\store;
use Livewire\Drawer\Utils;
use Livewire\ComponentHook;

class SupportNestedComponentListeners extends ComponentHook
{
    public function mount($params, $parent)
    {
        // If a Livewire component is passed an attribute with an "@"
        // (<livewire:child @some-event="handler")
        // Then turn it into an Alpine listener and add it to a
        // "attributes" key in the store so it can be added to the
        // component's memo and passed again to the server on subsequent
        // requests to ensure it is always added as an HTML attribute
        // to the root element of the component...
        foreach ($params as $key => $value) {
            if (str($key)->startsWith('@')) {
                // any kebab-cased parameters passed in will have been converted to camelCase
                // so we need to convert back to kebab to ensure events are valid in html
                $fullEvent = str($key)->after('@')->kebab();
                $attributeKey = 'x-on:'.$fullEvent;
                $attributeValue = "\$wire.\$parent.".$value;

                store($this->component)->push('attributes', $attributeValue, $attributeKey);
            }
        }
    }

    public function render($view, $data)
    {
        return function ($html, $replaceHtml) {
            $attributes = store($this->component)->get('attributes', false);

            if (! $attributes) return;

            foreach ($attributes as $key => $value) {
                $replaceHtml(Utils::insertAttributesIntoHtmlRoot($html, [
                    $key => $value,
                ]));
            }
        };
    }

    public function dehydrate($context)
    {
        $attributes = store($this->component)->get('attributes', false);

        if (! $attributes) return;

        $attributes && $context->addMemo('attributes', $attributes);
    }

    public function hydrate($memo)
    {
        if (! isset($memo['attributes'])) return;

        $attributes = $memo['attributes'];

        // Store the attributes for later dehydration...
        store($this->component)->set('attributes', $attributes);
    }
}
