<?php

namespace Livewire\Features\SupportEagerLoading;

use Livewire\Synthesizers\LivewireSynth;
use Livewire\Mechanisms\ComponentDataStore;
use Illuminate\Support\Str;
use Synthetic\Utils;

class SupportEagerLoading
{
    protected $eagerRenderers = [];

    public function boot()
    {
        app('livewire')->enableJsFeature('eager-loading');

        app('livewire')->directive('eager', function ($expression) {
            $key = Str::random(5);

            return <<<HTML
            <?php
                Livewire\Features\SupportEagerLoading\SupportEagerLoading::eagerLoad(\$__livewire, {$expression}, '{$key}');
                \$__oldEager = \$__eagerKey ?? null;
                \$__eagerKey = '{$key}';

                if (isset(\$__eagerRender)) echo '<!-- __EAGER:'.\$__eagerKey.'__ -->';
            ?><div wire:eager="{$key}">
            HTML;
        });

        app('livewire')->directive('endeager', function ($expression) {
            return <<<HTML
            </div>
            <?php
                if (isset(\$__eagerRender)) echo '<!-- __ENDEAGER:'.\$__eagerKey.'__ -->';
                if (\$__oldEager) {
                    \$__eagerKey = \$__oldEager;
                } else {
                    unset(\$__eagerKey);
                    unset(\$__oldEager);
                }
            ?>
            HTML;
        });

        app('synthetic')->on('mount', function($name, $params, $parent, $key, $slots, $hijack, $viewScope) {
            if (isset($viewScope['__eagerRender'])) {
                if (isset($viewScope['__eagerKey'])) {
                    // throw new \Exception('Cannot render a child component inside an eager block');
                } else {
                    $hijack('<div><!-- __EAGERDUMMYCHILD__ --></div>');
                }
            }
        });

        app('synthetic')->on('render', function ($target, $view, $data) {
            return function ($html) use ($target, $view) {
                if (! ComponentDataStore::has($target, 'eagers')) return $html;

                foreach (ComponentDataStore::get($target, 'eagers') as $key => $method) {
                    $this->eagerRenderers[$target->getId()] = function () use ($target, $method, $view, $key) {
                        $target->$method();

                        $view->__eagerRender = true;
                        $properties = Utils::getPublicPropertiesDefinedOnSubclass($target);
                        $view->with($properties);

                        $newHtml = $view->render();

                        unset($view->__eagerRender);

                        $partial = (string) str($newHtml)->between('__EAGER:'.$key.'__ -->', "\n<!-- __ENDEAGER:".$key.'__');

                        ComponentDataStore::push($target, 'eagerPartials', [
                            'partial' => $partial,
                            'key' => $key,
                            'method' => $method,
                        ]);
                    };
                }

                return $html;
            };
        });

        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            return function ($forward) use ($target, $context) {
                if (! isset($this->eagerRenderers[$target->getId()])) return $forward;

                $render = $this->eagerRenderers[$target->getId()];

                $render();

                if (! ComponentDataStore::has($target, 'eagerPartials')) return $forward;

                $context->addEffect('eager', ComponentDataStore::get($target, 'eagerPartials'));

                return $forward;
            };
        });
    }

    static function eagerLoad($component, $method, $key)
    {
        ComponentDataStore::push($component, 'eagers', $method, $key);
    }
}
