<?php

namespace Livewire\Mechanisms;

use Livewire\Manager;
use Livewire\Drawer\Utils;
use Livewire\Drawer\IsSingleton;
use Livewire\Drawer\ImplicitlyBoundMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;

class RenderComponent
{
    use IsSingleton;

    public static $renderStack = [];

    function boot()
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

echo app('livewire')->mount(\$__name, \$__params, $key, \$__slots ?? [], get_defined_vars());

unset(\$__name);
unset(\$__params);
unset(\$__split);
if (isset(\$__slots)) unset(\$__slots);
?>
EOT;
    }

    static function mount($name, $params = [], $key = null, $slots = [], $viewScope = [])
    {
        $parent = last(static::$renderStack);

        $hijackedHtml = null;
        $hijack = function ($html) use (&$hijackedHtml) { $hijackedHtml = $html; };

        if (isset($params['apply'])) {
            $params = [...$params, ...$params['apply']];
            unset($params['apply']);
        }

        $finishMount = app('synthetic')->trigger('mount', $name, $params, $parent, $key, $slots, $hijack, $viewScope);

        // Allow a "mount" event listener to short-circuit the mount...
        if ($hijackedHtml !== null) return $hijackedHtml;

        // If this has already been rendered spoof it...
        if ($parent && $parent->hasPreviouslyRenderedChild($key)) {
            [$tag, $childId] = $parent->getPreviouslyRenderedChild($key);

            $finish = app('synthetic')->trigger('dummy-mount', $tag, $childId, $params, $parent, $key);

            $html  = "<{$tag} wire:id=\"{$childId}\"></{$tag}>";

            $parent->setChild($key, $tag, $childId);

            return $finish($html);
        }

        // New up the component instance...

        // This is if a user doesn't pass params, BUT passes key() as the second argument...
        if (is_string($params)) $params = [];
        $id = str()->random(20);

        $target = app('livewire')->new($name);
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

        return $html;
    }

    static function renderComponentBladeView($target, $blade, $data)
    {
        array_push(static::$renderStack, $target);

        $view = static::getBladeView($blade, $data);

        $finish = app('synthetic')->trigger('render', $target, $view, $data);

        $view->with('__livewire', $target);

        $rawHtml = $view->render();

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
