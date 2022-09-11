<?php

namespace Livewire\Features\SupportLazyLoading;

use Illuminate\Support\Str;
use Livewire\Component;

class SupportLazyLoading
{
    public function boot()
    {
        app('livewire')->component('lazy', new class extends Component {
            /** @prop */
            public $link = true;

            public $firstTime = true;

            public $forwards;

            public $show = false;

            public function render()
            {
                return <<<'HTML'
                <div x-intersect="$wire.show = true; $wire.firstTime = false; $wire.$commit()">
                    @if ($show || ! $firstTime)
                        {{ $slot($forwards) }}
                    @else
                        {{ $loading($forwards) }}
                    @endif
                </div>
                HTML;
            }
        });

        $lazy = '\B@(@?lazy(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?';
        $endlazy = '@endlazy';

        app('livewire')->precompiler('/'.$lazy.'(?<body>.*)'.$endlazy.'/xsm', function ($matches) {
            $body = $matches['body'];
            $loading = '<div>Loading...</div>';
            $params = $matches[4] ?: '[]';
            $paramsWithComma = $matches[4] ? (', '.$matches[4]) : '';

            [$opening, $closing] = str($matches[0])->explode($body);

            if (str($body)->contains('@loading')) {
                [$body, $loading] = explode('@loading', $body);
            }

            $hash = Str::random(20);

            $path = storage_path('framework/cache/livewire-'.$hash.'.blade.php');
            file_put_contents($path, $body);

            return <<<HTML
                <livewire:lazy :link="true" :forwards="{$params}" :scope="[__all__]">
                    {$body}

                    @slot('loading', null{$paramsWithComma})
                        {$loading}
                    @endslot
                </livewire:lazy>
                HTML;
        });
    }
}
