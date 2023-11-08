<?php

namespace Livewire\Features\SupportStaticPartials;

use Illuminate\Support\Facades\Blade;
use function Livewire\store;
use Livewire\ComponentHook;
use function Livewire\on;

class SupportStaticPartials extends ComponentHook
{
    static $isEnabled = true;

    static function enable()
    {
        static::$isEnabled = true;
    }

    static function provide()
    {
        if (! static::$isEnabled) return;

        on('flush-state', function () {
            static::$isEnabled = false;
        });

        Blade::directive('static', function () {
            $key = str()->random(10);

            return "<?php \$__livewire->startStatic('$key'); ?>";
        });

        Blade::directive('endstatic', function () {
            return "<?php echo \$__livewire->endStatic(); ?>";
        });

        Blade::directive('dynamic', function () {
            return "<?php \$__livewire->startDynamic(); ?>";
        });

        Blade::directive('enddynamic', function () {
            return "<?php echo \$__livewire->endDynamic(); ?>";
        });
    }

    function hydrate($memo) {
        // Store previously rendered statics so they can be bypassed when rendering this time...
        if (isset($memo['statics'])) {
            $this->component->setPreviousStatics($memo['statics']);
        }
    }

    function dehydrate($context)
    {
        // Store "statics" for referencing on the next request...
        $context->addMemo('statics', $this->component->getAllStatics());

        // Log any "new statics" so JavaScript can cache them for the future...
        $context->addEffect('newStatics', $this->component->getNewStatics());

        // Log "renderedStatics" so they can be re-injected by JavaScript before morphing...
        $context->addEffect('bypassedStatics', $this->component->getBypassedStatics());
    }
}
