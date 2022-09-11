<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Livewire\Synthesizers\LivewireSynth;
use Illuminate\Support\Facades\Blade;

class SupportAutoInjectedAssets
{
    public static $hasRenderedAComponentThisRequest = false;

    public function boot()
    {
        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            static::$hasRenderedAComponentThisRequest = true;
        });

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);

        $kernel->pushMiddleware(function ($request, $next) {
            $response = $next($request);

            if (! app('livewire')->isDefinitelyLivewireRequest() && static::hasRenderedAComponentThisRequest()) {
                $content = $response->getContent();

                $response->setContent(
                    $this->injectAssets($content)
                );

                return $response;
            }

            return $response;
        });
    }

    public function injectAssets($content)
    {
        return Blade::render('@livewireStyles').$content.Blade::render('@livewireScripts');
    }

    static function hasRenderedAComponentThisRequest()
    {
        return static::$hasRenderedAComponentThisRequest;
    }
}
