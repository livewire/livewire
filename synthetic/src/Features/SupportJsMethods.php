<?php

namespace Synthetic\Features;

use Synthetic\Synthesizers\ObjectSynth;
use Illuminate\Http\RedirectResponse;
use ReflectionMethod;
use ReflectionClass;

use function Livewire\on;

class SupportJsMethods
{
    public function __invoke()
    {
        on('call', function ($synth, $target, $method, $params, $addEffect) {
            if (! $synth instanceof ObjectSynth) return;

            return function ($result) use ($method, $params, $addEffect) {
                if (! $result instanceof RedirectResponse) return $result;

                $addEffect('redirect', $result->getTargetUrl());

                return $result;
            };
        });

        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof ObjectSynth) return;

            $methods = $this->getJsMethods($target);

            if (! $methods) return;

            if ($context->initial) {
                $context->addEffect('js', collect($methods)->mapWithKeys(function ($method) use ($target) {
                    return [$method => $target->$method()];
                }));
            }
        });
    }

    function getJsMethods($target)
    {
        $methods = (new ReflectionClass($target))->getMethods(ReflectionMethod::IS_PUBLIC);

        return collect($methods)
            ->filter(function ($subject) {
                return $subject->getDocComment() && str($subject->getDocComment())->contains('@js');
            })
            ->map(function ($subject) use ($target) {
                return $subject->getName();
            })
            ->toArray();
    }
}
