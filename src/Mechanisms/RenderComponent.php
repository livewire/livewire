<?php

namespace Livewire\Mechanisms;

use Illuminate\Contracts\Container\BindingResolutionException;
use Livewire\Manager;
use Livewire\Drawer\Utils;
use Livewire\Drawer\ImplicitlyBoundMethod;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Blade;
use Throwable;

use function Livewire\store;
use function Synthetic\trigger;
use function Synthetic\wrap;

class RenderComponent
{
    public static $renderStack = [];

    function boot()
    {
        app()->singleton($this::class);

        Blade::directive('livewire', [static::class, 'livewire']);

        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if ($context->initial) return;
            if (! $synth instanceof \Livewire\LivewireSynth) return;

            if (! store($target)->get('skipRender', false)) {
                $rendered = method_exists($target, 'render')
                    ? wrap($target)->render()
                    : view("livewire.{$target->getName()}");

                $properties = Utils::getPublicPropertiesDefinedOnSubclass($target);
                $html = static::renderComponentBladeView($target, $rendered, $properties);

                $context->addEffect('html', $html);
            }
        });
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

        [$receiveInstance, $finishMount] = app('synthetic')->trigger('mount', $name, $params, $parent, $key, $hijack);

        if ($hijackedHtml !== null) return [$hijackedHtml];

        // Now we're ready to actually create a Livewire component instance...
        $component = app(ComponentRegistry::class)->new($name, $params);

        $receiveInstance($component);

        $html = '<div></div>';

        if (! store($component)->get('skipRender', false)) {
            $rendered = method_exists($component, 'render')
                ? wrap($component)->render()
                : view("livewire.{$component->getName()}");

            $properties = Utils::getPublicPropertiesDefinedOnSubclass($component);
            $html = static::renderComponentBladeView($component, $rendered, $properties);
        }

        // Trigger the dehydrate...
        $payload = app('synthetic')->synthesize($component);

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
