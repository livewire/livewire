<?php

namespace Livewire\Mechanisms;

use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Drawer\ImplicitlyBoundMethod;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Livewire\Drawer\Utils;
use Livewire\Manager;
use Throwable;

use function Livewire\on;
use function Livewire\trigger;
use function Livewire\store;
use function Livewire\wrap;

class RenderComponent
{
    public static $renderStack = [];

    function boot()
    {
        app()->singleton($this::class);

        Blade::directive('livewire', [static::class, 'livewire']);

        on('dehydrate', function ($synth, $target, $context) {
            if ($context->initial) return;
            if (! $synth instanceof \Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth) return;

            $html = static::render($target);

            $html && $context->addEffect('html', $html);
        });
    }

    public static function livewire($expression)
    {
        $key = "'" . Str::random(7) . "'";

        $pattern = "/,\s*?key\(([\s\S]*)\)/"; //everything between ",key(" and ")"

        $expression = preg_replace_callback($pattern, function ($match) use (&$key) {
            $key = trim($match[1]) ?: $key;
            return "";
        }, $expression);

        // If we are inside a Livewire component, we know we're rendering a child.
        // Therefore, we must create a more deterministic view cache key so that
        // Livewire children are properly tracked across load balancers.
        // if (Manager::$currentCompilingViewPath !== null) {
        //     // $key = '[hash of Blade view path]-[current @livewire directive count]'
        //     $key = "'l" . crc32(Manager::$currentCompilingViewPath) . "-" . Manager::$currentCompilingChildCounter . "'";

        //     // We'll increment count, so each cache key inside a compiled view is unique.
        //     Manager::$currentCompilingChildCounter++;
        // }

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
        // Support for "spreading" or "applying" an array of parameters by a single "apply" key.
        // Used so far exclusively for forwarding properties by the "SupportsLazy" feature...
        if (isset($params['apply'])) {
            $params = [...$params, ...$params['apply']];

            unset($params['apply']);
        }

        // Grab the parent component that this component is mounted within (if one exists)...
        $parent = last(static::$renderStack);

        // Provide a way to interupt a mounting component and render entirely different html...
        $hijackedHtml = null;
        $hijack = function ($html) use (&$hijackedHtml) { $hijackedHtml = $html; };

        [$receiveInstance, $finishMount] = trigger('mount', $name, $params, $parent, $key, $hijack);

        if ($hijackedHtml !== null) return [$hijackedHtml];

        // Now we're ready to actually create a Livewire component instance...
        $component = app(ComponentRegistry::class)->new($name, $params);

        $receiveInstance($component);

        $html = static::render($component);

        // When "skipRender" is called in "mount" from a subsequent request...
        if (! $html) $html = '<div></div>';

        // Trigger the dehydrate...
        $payload = app('livewire')->snapshot($component, initial: true);

        // Remove it from effects...
        if (isset($payload['effects']['']['html'])) unset($payload['effects']['']['html']);

        $html = Utils::insertAttributesIntoHtmlRoot($html, [
            'wire:initial-data' => $payload,
        ]);

        $finishMount($html);

        return [$html, $payload];
    }

    static function render($target)
    {
        if (store($target)->get('skipRender', false)) return;

        $blade = method_exists($target, 'render')
            ? wrap($target)->render()
            : view("livewire.{$target->getName()}");

        $properties = Utils::getPublicPropertiesDefinedOnSubclass($target);

        array_push(static::$renderStack, $target);

        $view = static::getBladeView($blade, $properties);

        $finish = trigger('render', $target, $view, $properties);

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
