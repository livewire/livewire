<?php

namespace Livewire\Features\SupportStacks;

use Illuminate\Support\Str;
use Livewire\ComponentHook;
use Illuminate\Support\Facades\Blade;

use function Livewire\on;

class SupportStacks extends ComponentHook
{
    protected static $forStack = [];

    static function provide()
    {
        on('flush-state', function () {
            static::$forStack = [];
        });

        Blade::directive('stack', function ($name, $default = '') {
            $expression = rtrim("{$name}, {$default}", ', ');

            return "
                <template livewire-stack=\"<?php echo {$name}; ?>\"></template>
                <?php echo \$__env->yieldPushContent($expression); ?>
                <template livewire-end-stack=\"<?php echo {$name}; ?>\"></template>
            ";
        });

        Blade::directive('once', function ($id = null) {
            $id = $id ?: "'".(string) Str::uuid()."'";

            return "<?php
                if (isset(\$_instance)) \$__stack_once = true;
    
                if (! \$__env->hasRenderedOnce({$id})): \$__env->markAsRenderedOnce({$id});
            ?>";
        });

        Blade::directive('endonce', function () {
            return "<?php
                endif;

                if (isset(\$_instance) && isset(\$__stack_once)) unset(\$__stack_once);
            ?>";
        });

        Blade::directive('push', function ($name, $content = '') {
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
        });

        Blade::directive('endpush', function () {
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
        });

        Blade::directive('prepend', function ($name, $content = '') {
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
        });

        Blade::directive('endprepend', function () {
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
        });
    }

    public function addToStack($stack, $type, $contents, $key = null)
    {
        static::$forStack[] = [
            'key' => $key ?: $this->getId(),
            'stack' => $stack,
            'type' => $type,
            'contents' => $contents,
        ];
    }

    function dehydrate($context)
    {
        if (count(static::$forStack)) {
            $context->pushEffect('forStack', static::$forStack, $context->component->getName());
        }

        $context->addMemo('forStack', static::$forStack);
    }

}