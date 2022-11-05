<?php

namespace Livewire\Mechanisms;

use Livewire\Manager;
use Livewire\Drawer\Utils;
use Livewire\Drawer\ImplicitlyBoundMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;

class RenderComponent
{
    public static $renderStack = [];

    function boot()
    {
        app()->singleton($this::class);

        Blade::directive('livewire', [static::class, 'livewire']);
    }

    public static function livewire($expression)
    {
        $key = "'" . Str::random(7) . "'";

        // If we are inside a Livewire component, we know we're rendering a child.
        // Therefore, we must create a more deterministic view cache key so that
        // Livewire children are properly tracked across load balancers.
        // if (Manager::$currentCompilingViewPath !== null) {
        //     // $key = '[hash of Blade view path]-[current @livewire directive count]'
        //     $key = "'l" . crc32(Manager::$currentCompilingViewPath) . "-" . Manager::$currentCompilingChildCounter . "'";

        //     // We'll increment count, so each cache key inside a compiled view is unique.
        //     Manager::$currentCompilingChildCounter++;
        // }

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

[\$__html] = app('livewire')->mount(\$__name, \$__params, $key, \$__slots ?? [], get_defined_vars());

echo \$__html;

unset(\$__html);
unset(\$__name);
unset(\$__params);
unset(\$__split);
if (isset(\$__slots)) unset(\$__slots);
?>
EOT;
    }

    static function mount($name, $params = [], $key = null)
    {
        // This is if a user doesn't pass params, BUT passes key() as the second argument...
        if (is_string($params)) $params = [];

        $parent = last(static::$renderStack);

        $hijackedHtml = null;
        $hijack = function ($html) use (&$hijackedHtml) { $hijackedHtml = $html; };

        if (isset($params['apply'])) {
            $params = [...$params, ...$params['apply']];
            unset($params['apply']);
        }

        [$receiveInstance, $finishMount] = app('synthetic')->trigger('mount', $name, $params, $parent, $key, $hijack);

        // Allow a "mount" event listener to short-circuit the mount...
        if ($hijackedHtml !== null) return [$hijackedHtml];

        $component = app(ComponentRegistry::class)->new($name);

        foreach ($params as $name => $value) {
            if (property_exists($component, $name)) {
                $component->$name = $value;
            }
        }

        $receiveInstance($component);

        // Render it...
        $payload = app('synthetic')->synthesize($component);

        $html = $payload['effects']['']['html'] ?? '<div></div>';

        if ($parent) {
            preg_match('/<([a-zA-Z0-9\-]*)/', $html, $matches, PREG_OFFSET_CAPTURE);
            $tag = $matches[1][0];
            $parent->setChild($key, $tag, $component->getId());
        }

        // Remove it from effects...
        if (isset($payload['effects']['']['html'])) unset($payload['effects']['']['html']);

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            'wire:initial-data' => $payload,
        ]);

        $finishMount($html);

        return [$html, $payload];
    }

    static function renderComponentBladeView($target, $blade, $data)
    {
        array_push(static::$renderStack, $target);

        $view = static::getBladeView($blade, $data);

        $finish = app('synthetic')->trigger('render', $target, $view, $data);

        $revertA = Utils::shareWithViews('__livewire', $target);
        $revertB = Utils::shareWithViews('_instance', $target); // @legacy

        $rawHtml = $view->render();

        $revertA();
        $revertB();

        $rawHtml = $finish($rawHtml);

        array_pop(static::$renderStack);

        $html = Utils::insertAttributesIntoHtmlRoot($rawHtml, [ 'wire:id' => $target->getId() ]);

        return $html;
    }

    static function getBladeView($subject, $data = [])
    {
        if (! is_string($subject)) {
            return tap($subject)->with($data);
        }

        $component = new class($subject) extends \Illuminate\View\Component
        {
            protected $template;

            public function __construct($template)
            {
                $this->template = $template;
            }

            public function render()
            {
                return $this->template;
            }
        };

        $view = app('view')->make($component->resolveView(), $data);

        return $view;
    }
}
