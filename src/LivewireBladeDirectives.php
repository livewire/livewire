<?php

namespace Livewire;

use Illuminate\Support\Str;

class LivewireBladeDirectives
{
    public static function this()
    {
        return "window.livewire.find('{{ \$_instance->id }}')";
    }

    public static function entangle($expression)
    {
        return <<<EOT
<?php if ((object) ({$expression}) instanceof \Livewire\WireDirective) : ?>window.Livewire.find('{{ \$_instance->id }}').entangle('{{ {$expression}->value() }}'){{ {$expression}->hasModifier('defer') ? '.defer' : '' }}<?php else : ?>window.Livewire.find('{{ \$_instance->id }}').entangle('{{ {$expression} }}')<?php endif; ?>
EOT;
    }

    public static function js($expression)
    {
        return <<<EOT
<?php
    if (is_object({$expression}) || is_array({$expression})) {
        echo "JSON.parse(atob('".base64_encode(json_encode({$expression}))."'))";
    } elseif (is_string({$expression})) {
        echo "'".str_replace("'", "\'", {$expression})."'";
    } else {
        echo json_encode({$expression});
    }
?>
EOT;
    }

    public static function livewireStyles($expression)
    {
        return '{!! \Livewire\Livewire::styles('.$expression.') !!}';
    }

    public static function livewireScripts($expression)
    {
        return '{!! \Livewire\Livewire::scripts('.$expression.') !!}';
    }

    public static function livewire($expression)
    {
        $cachedKey = "'" . Str::random(7) . "'";

        // If we are inside a Livewire component, we know we're rendering a child.
        // Therefore, we must create a more deterministic view cache key so that
        // Livewire children are properly tracked across load balancers.
        if (LivewireManager::$currentCompilingViewPath !== null) {
            // $cachedKey = '[hash of Blade view path]-[current @livewire directive count]'
            $cachedKey = "'l" . crc32(LivewireManager::$currentCompilingViewPath) . "-" . LivewireManager::$currentCompilingChildCounter . "'";

            // We'll increment count, so each cache key inside a compiled view is unique.
            LivewireManager::$currentCompilingChildCounter++;
        } 

        $pattern = "/,\s*?key\(([\s\S]*)\)/"; //everything between ",key(" and ")"
        $expression = preg_replace_callback($pattern, function ($match) use (&$cachedKey) {
            $cachedKey = trim($match[1]) ?: $cachedKey;
            return "";
        }, $expression);

        return <<<EOT
<?php
if (! isset(\$_instance)) {
    \$html = \Livewire\Livewire::mount({$expression})->html();
} elseif (\$_instance->childHasBeenRendered($cachedKey)) {
    \$componentId = \$_instance->getRenderedChildComponentId($cachedKey);
    \$componentTag = \$_instance->getRenderedChildComponentTagName($cachedKey);
    \$html = \Livewire\Livewire::dummyMount(\$componentId, \$componentTag);
    \$_instance->preserveRenderedChild($cachedKey);
} else {
    \$response = \Livewire\Livewire::mount({$expression});
    \$html = \$response->html();
    \$_instance->logRenderedChild($cachedKey, \$response->id(), \Livewire\Livewire::getRootElementTagName(\$html));
}
echo \$html;
?>
EOT;
    }

    public static function stack($name, $default = '') {
        $expression = rtrim("{$name}, {$default}", ', ');

        return "
            <template livewire-stack=\"<?php echo {$name}; ?>\"></template>
            <?php echo \$__env->yieldPushContent($expression); ?>
            <template livewire-end-stack=\"<?php echo {$name}; ?>\"></template>
        ";
    }

    public static function once($id = null) {
        $id = $id ?: "'".(string) Str::uuid()."'";

        return "<?php
            if (isset(\$_instance)) \$__stack_once = true;

            if (! \$__env->hasRenderedOnce({$id})): \$__env->markAsRenderedOnce({$id});
        ?>";
    }

    public static function endonce() {
        return "<?php
            endif;

            if (isset(\$_instance) && isset(\$__stack_once)) unset(\$__stack_once);
        ?>";
    }

    public static function push($name, $content = '') {
        $randomKey = Str::random(9);
        $expression = rtrim("{$name}, {$content}", ', ');

        return "<?php
            if (isset(\$_instance)) {
                \$__stack_item_key = isset(\$__stack_once) ? crc32(\$__path) : '{$randomKey}';

                \$__env->startPush({$expression});

                \$__stack_name = {$name};

                ob_start();

                echo '<template livewire-stack-key=\"'.\$__stack_item_key.'\"></template>';
            } else {
                \$__env->startPush({$expression});
            }
        ?>";
    }

    public static function prepend($name, $content = '') {
        $randomKey = Str::random(9);
        $expression = rtrim("{$name}, {$content}", ', ');

        return "<?php
            if (isset(\$_instance)) {
                \$__stack_item_key = isset(\$__stack_once) ? crc32(\$__path) : '{$randomKey}';

                \$__env->startPrepend({$expression});

                \$__stack_name = {$name};

                ob_start();

                echo '<template livewire-stack-key=\"'.\$__stack_item_key.'\"></template>';
            } else {
                \$__env->startPrepend({$expression});
            }
        ?>";
    }

    public static function endpush() {
        return "<?php
            if (isset(\$_instance)) {
                \$__contents = ob_get_clean();

                \$_instance->addToStack(\$__stack_name, 'push', \$__contents, \$__stack_item_key);

                echo \$__contents;
                unset(\$__contents);

                unset(\$__stack_item_key);
                unset(\$__stack_name);

                \$__env->stopPush();
            } else {
                \$__env->stopPush();
            }
        ?>";
    }

    public static function endprepend() {
        return "<?php
            if (isset(\$_instance)) {
                \$__contents = ob_get_clean();

                \$_instance->addToStack(\$__stack_name, 'prepend', \$__contents, \$__stack_item_key);

                echo \$__contents;
                unset(\$__contents);

                unset(\$__stack_item_key);
                unset(\$__stack_name);

                \$__env->stopPrepend();
            } else {
                \$__env->stopPrepend();
            }
        ?>";
    }
}
