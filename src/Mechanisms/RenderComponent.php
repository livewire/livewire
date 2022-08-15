<?php

namespace Livewire\Mechanisms;

use Livewire\Utils;
use Livewire\ImplicitlyBoundMethod;
use Livewire\Features\SupportReactiveProps;
use Illuminate\Support\Facades\Blade;

class RenderComponent
{
    public static $renderStack = [];

    static function mount($name, $params = [], $key = null)
    {
        $parent = last(static::$renderStack);

        // If this has already been rendered spoof it...
        if ($parent && $parent->hasChild($key)) {
            [$tag, $childId] = $parent->getChild($key);

            SupportReactiveProps::storeChildParams($childId, $params);

            return "<{$tag} wire:id=\"{$childId}\"></{$tag}>";
        }

        // New up the component instance...

        // This is if a user doesn't pass params, BUT passes key() as the second argument.
        if (is_string($params)) $params = [];
        $id = str()->random(20);
        if (! class_exists($name)) throw new \Exception('Not a class');
        $target = new $name;
        $target->setId($id);

        if ($params) {
            // $target->__props = array_keys($params);

            foreach ($params as $name => $value) {
                $target->$name = $value;
            }
        }

        if (method_exists($target, 'mount')) {
            ImplicitlyBoundMethod::call(app(), [$target, 'mount'], $params);
        }

        // Render it...
        $payload = app('synthetic')->synthesize($target);

        $html = $payload['effects']['']['html'];

        // Remove it from effects...
        unset($payload['effects']['']['html']);

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            'wire:initial-data' => $payload,
        ]);

        // If this is within a parent, track that...
        if ($parent) {
            preg_match('/<([a-zA-Z0-9\-]*)/', $html, $matches, PREG_OFFSET_CAPTURE);
            $tag = $matches[1][0];
            $parent->setChild($key, $tag, $id);
        }

        return $html;
    }

    static function renderComponentBladeView($target, $blade, $viewData)
    {
        array_push(static::$renderStack, $target);

        $rawHTML = Blade::render($blade, [
            ...$viewData
        ]);

        array_pop(static::$renderStack);

        $html = Utils::insertAttributesIntoHtmlRoot($rawHTML, [ 'wire:id' => $target->getId() ]);

        return $html;
    }
}


// Props:
// Rendering a root
// Render a child and pass any params
// Make two lists:
// - all children
// - dependant children
// When rendering a child:
  // - initialize the properties with props passed in
  // - generate a hash list of props to track mutations and such


// Subsequent child render:
// Render like normal (only this single component), because props are immutable we're good
// Make sure no props were mutated during the request

// Subsequent root render:
// send a this component along with any dependant component payloads to the server
// starting by rendering top-down order
// render the root and when a child is encountered, check the hashes for differences
// if there is no difference skip the render entirely
// if there is a difference, hydrate child and update the prop value because calling or rendering
// render the child and do the whole bit over again
