<?php

namespace Synthetic\Features;

use Illuminate\Http\RedirectResponse;
use Synthetic\Synthesizers\ObjectSynth;

class SupportRedirects
{
    public function __invoke()
    {
        app('synthetic')->on('call', function ($synth, $target, $method, $params, $addEffect) {
            if (! $synth instanceof ObjectSynth) return;

            return function ($result) use ($method, $params, $addEffect) {
                if (! $result instanceof RedirectResponse) return $result;

                $addEffect('redirect', $result->getTargetUrl());

                return $result;
            };
        });
    }
}
