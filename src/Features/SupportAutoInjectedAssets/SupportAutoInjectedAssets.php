<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Livewire\Mechanisms\UpdateComponents\Synthesizers\LivewireSynth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\Http\Events\RequestHandled;

use function Livewire\on;

class SupportAutoInjectedAssets
{
    public static $hasRenderedAComponentThisRequest = false;

    public function boot()
    {
        on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;

            static::$hasRenderedAComponentThisRequest = true;
        });

        app('events')->listen(RequestHandled::class, function ($handled) {
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;
            if ($handled->response->status() !== 200) return;

            $html = $handled->response->getContent();

            if (str($html)->contains('</html>')) {
                $handled->response->setContent($this->injectAssets($html));
            } else {
                //
            }
        });
    }

    public function injectAssets($html)
    {
        $replacement = Blade::render('@livewireScripts').'</html>';
        $html = str($html)->replaceLast('</html>', $replacement);

        return Blade::render('@livewireStyles').$html;
    }

    static function hasRenderedAComponentThisRequest()
    {
        return static::$hasRenderedAComponentThisRequest;
    }
}
