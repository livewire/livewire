<?php

namespace Livewire\Features;

use function Livewire\invade;
use Illuminate\Support\Str;

class SupportLazyLoading
{
    public function boot()
    {
        $compiler = function ($string) {
            $pattern = '/@lazy\((?<params>[^\)]*)\)(?<body>.*)@endlazy/sm';

            return preg_replace_callback($pattern, function ($matches) use ($string) {
                $body = $matches['body'];
                $loading = '<div>Loading...</div>';
                $params = $matches['params'];

                [$opening, $closing] = str($matches[0])->explode($body);

                if (str($body)->contains('@loading')) {
                    [$body, $loading] = explode('@loading', $body);
                }

                $hash = Str::random(20);

                $path = storage_path('framework/cache/livewire-'.$hash.'.blade.php');
                file_put_contents($path, $body);

                return <<<HTML
                    <livewire:lazyy :forwards="$params" :scope="[__all__]">
                        $body

                        @slot('loading', null, $params)
                            $loading
                        @endslot
                    </livewire:lazyy>
                    HTML;
            }, $string);
        };

        // This ensures we'll be at the top of the precompilers...
        invade(app('blade.compiler'))->precompilers = [$compiler, ...invade(app('blade.compiler'))->precompilers];
    }
}
