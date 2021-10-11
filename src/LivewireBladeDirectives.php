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
        echo "atob('".base64_encode(json_encode({$expression}))."')";
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
        $lastArg = str(last(explode(',', $expression)))->trim();

        if ($lastArg->startsWith('key(') && $lastArg->endsWith(')')) {
            $cachedKey = $lastArg->replaceFirst('key(', '')->replaceLast(')', '');
            $args = explode(',', $expression);
            array_pop($args);
            $expression = implode(',', $args);
        } else {
            $cachedKey = "'".str()->random(7)."'";
        }

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
        return "
            <template livewire-stack=\"<?php echo {$name}; ?>\"></template>
            <?php echo \$__env->yieldPushContent({$name}, ${default}); ?>
            <template livewire-end-stack=\"<?php echo {$name}; ?>\"></template>
        ";
    }

    public static function once($id = null) {
        $id = $id ?: "'".(string) Str::uuid()."'";

        return "<?php
            if (isset(\$_instance)) \$_started_once = true;

            if (! \$__env->hasRenderedOnce({$id})): \$__env->markAsRenderedOnce({$id});
        ?>";
    }

    public static function endonce() {
        return "<?php
            endif;

            if (isset(\$_instance) && isset(\$_started_once)) unset(\$_started_once);
        ?>";
    }

    public static function push($name, $content = '') {
        return "<?php
            if (isset(\$_instance)) {
                \$_key = isset(\$_started_once) ? \$_instance->getName() : null;

                \$__env->startPush({$name}, {$content});

                \$_push_name = {$name};

                ob_start();

                echo '<template livewire-stack-key=\"'.\$_key.'\"></template>';

                unset(\$_key);
            } else {
                \$__env->startPush({$name}, {$content});
            }
        ?>";
    }

    public static function prepend($name, $content = '') {
        return "<?php
            if (isset(\$_instance)) {
                \$_key = isset(\$_started_once) ? \$_instance->getName() : null;

                \$__env->startPrepend({$name}, {$content});

                \$_push_name = {$name};

                ob_start();

                echo '<template livewire-stack-key=\"'.\$_key.'\"></template>';

                unset(\$_key);
            } else {
                \$__env->startPrepend({$name}, {$content});
            }
        ?>";
    }

    public static function endpush() {
        return "<?php
            if (isset(\$_instance)) {
                \$__contents = ob_get_clean();
                
                \$_key = isset(\$_started_once) ? \$_instance->getName() : null;
                
                \$_instance->addToStack(\$_push_name, 'push', \$__contents, \$_key);

                echo \$__contents;
                unset(\$__contents);

                unset(\$_key);
                unset(\$_push_name);

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
                
                \$_key = isset(\$_started_once) ? \$_instance->getName() : null;
                
                \$_instance->addToStack(\$_push_name, 'prepend', \$__contents, \$_key);

                echo \$__contents;
                unset(\$__contents);

                unset(\$_key);
                unset(\$_push_name);

                \$__env->stopPrepend();
            } else {
                \$__env->stopPrepend();
            }
        ?>";
    }
}
