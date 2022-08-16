<?php

namespace Livewire\Mechanisms;

use Livewire\Utils;
use Livewire\ImplicitlyBoundMethod;
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

            $finish = app('synthetic')->trigger('dummy-mount', $tag, $childId, $params, $parent, $key);

            $html  = "<{$tag} wire:id=\"{$childId}\"></{$tag}>";

            return $finish($html);
        }

        // New up the component instance...

        // This is if a user doesn't pass params, BUT passes key() as the second argument.
        if (is_string($params)) $params = [];
        $id = str()->random(20);
        if (! class_exists($name)) throw new \Exception('Not a class');
        $target = new $name;
        $target->setId($id);

        $finish = app('synthetic')->trigger('mount', $target, $id, $params, $parent, $key);

        if ($params) {
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

        if ($parent) {
            preg_match('/<([a-zA-Z0-9\-]*)/', $html, $matches, PREG_OFFSET_CAPTURE);
            $tag = $matches[1][0];
            $parent->setChild($key, $tag, $id);
        }

        // Remove it from effects...
        unset($payload['effects']['']['html']);

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            'wire:initial-data' => $payload,
        ]);

        return $finish($html);
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
