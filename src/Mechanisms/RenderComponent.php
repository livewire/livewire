<?php

namespace Livewire\Mechanisms;

use Livewire\Utils;
use Livewire\Manager;
use Livewire\ImplicitlyBoundMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;

class RenderComponent
{
    public static $renderStack = [];

    public function __invoke()
    {
        Blade::directive('livewire', [static::class, 'livewire']);
    }

    public static function livewire($expression)
    {
        $key = "'" . Str::random(7) . "'";

        // If we are inside a Livewire component, we know we're rendering a child.
        // Therefore, we must create a more deterministic view cache key so that
        // Livewire children are properly tracked across load balancers.
        if (Manager::$currentCompilingViewPath !== null) {
            // $key = '[hash of Blade view path]-[current @livewire directive count]'
            $key = "'l" . crc32(Manager::$currentCompilingViewPath) . "-" . Manager::$currentCompilingChildCounter . "'";

            // We'll increment count, so each cache key inside a compiled view is unique.
            Manager::$currentCompilingChildCounter++;
        }

        $pattern = "/,\s*?key\(([\s\S]*)\)/"; //everything between ",key(" and ")"
        $expression = preg_replace_callback($pattern, function ($match) use (&$key) {
            $key = trim($match[1]) ?: $key;
            return "";
        }, $expression);

        return <<<EOT
<?php
\$__split = function (\$name, \$params = []) {
    return [\$name, \$params];
};
[\$__name, \$__params] = \$__split($expression);

echo \Livewire\Mechanisms\RenderComponent::mount(\$__name, \$__params, $key, \$__slots ?? []);

unset(\$__name);
unset(\$__params);
unset(\$__split);
if (isset(\$__slots)) unset(\$__slots);
?>
EOT;
    }

    static function mount($name, $params = [], $key = null, $slots = [])
    {
        $parent = last(static::$renderStack);

        $hijackedHtml = null;
        $hijack = function ($html) use (&$hijackedHtml) { $hijackedHtml = $html; };

        $finishMount = app('synthetic')->trigger('mount', $name, $params, $parent, $key, $slots, $hijack);

        // Allow a "mount" event listener to short-circuit the mount...
        if ($hijackedHtml !== null) return $hijackedHtml;

        // If this has already been rendered spoof it...
        if ($parent && $parent->hasChild($key)) {
            [$tag, $childId] = $parent->getChild($key);

            $finish = app('synthetic')->trigger('dummy-mount', $tag, $childId, $params, $parent, $key);

            $html  = "<{$tag} wire:id=\"{$childId}\"></{$tag}>";

            return $finish($html);
        }

        // New up the component instance...

        // This is if a user doesn't pass params, BUT passes key() as the second argument...
        if (is_string($params)) $params = [];
        $id = str()->random(20);
        if (! class_exists($name)) throw new \Exception('Not a class');
        $target = new $name;
        $target->setId($id);

        $finishMount($target);

        if ($params) {
            foreach ($params as $name => $value) {
                $target->$name = $value;
            }
        }

        if (method_exists($target, 'mount')) {
            ImplicitlyBoundMethod::call(app(), [$target, 'mount'], $params);
        }

        $finish = app('synthetic')->trigger('render', $target, $id, $params, $parent, $key);

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
            '__livewire' => $target,
            ...$viewData
        ]);

        array_pop(static::$renderStack);

        $html = Utils::insertAttributesIntoHtmlRoot($rawHTML, [ 'wire:id' => $target->getId() ]);

        return $html;
    }
}
