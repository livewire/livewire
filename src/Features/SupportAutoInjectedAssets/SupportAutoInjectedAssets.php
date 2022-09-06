<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Illuminate\Support\Facades\Blade;

class SupportAutoInjectedAssets
{
    public function boot()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);

        $kernel->pushMiddleware(function ($request, $next) {
            $response = $next($request);

            if (! app('livewire')->isRenderingPageComponent()) {
                return $next($request);
            }

            $content = $response->getContent();

            $response->setContent(
                $this->injectAssets($content)
            );

            return $response;
        });
    }

    public function injectAssets($content)
    {
        return Blade::render('@livewireStyles').$content.Blade::render('@livewireScripts');
    }
}
