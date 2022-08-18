<?php

namespace Livewire\Features;

use Livewire\Synthesizers\LivewireSynth;
use Livewire\Mechanisms\ComponentDataStore;
use Illuminate\Support\Facades\Blade;

use function Livewire\invade;

// This is the current behavior. The problem is that it only
// currently supports child scope. It COULD support parent
// but then it wouldn't support child lol. Both can only
// be supported if the slots are completely static.
//
// @php
//     $foo = 'bar';
// @endphp

// <livewire:counter :slot="[$count]">
//     <div>This will work and live update: {{ $count }}!</div>

//     <h1>Unfortunately, this won't work: {{ $foo }}</h1>
// </livewire>

class SupportSlots
{
    public function __invoke()
    {
        $compiler = function ($string) {
            $pattern = '/\<livewire:[^\>]*\>(?<slot>.*)\<\/livewire\>/sm';

            return preg_replace_callback($pattern, function (array $matches) use ($string) {
                $whole = $matches[0];
                $slot = $matches['slot'];
                $hash = sha1($slot);

                $path = storage_path('framework/cache/livewire-'.$hash.'.blade.php');
                file_put_contents($path, $slot);

                [$opening, $closing] = str($whole)->explode($slot);

                $scope = '';
                $strippedScope = '';

                // Look in the opening for a slot scope declaration...
                if (str($opening)->contains(':slot')) {
                    $scope = (string) str($opening)->match('/:slot="\[(.*)\]"/');

                    $strippedScope = str($scope)->explode(',')->map(fn ($i) => trim($i, ' '))->map(fn ($i) => trim($i, '$'))->join(',');

                    $opening = (string) str($opening)->replace(':slot="['.$scope.']"', '');
                }

                return "<?php \$__slot = '$hash----$strippedScope'; ?>\n".$opening;
            }, $string);
        };

        // This ensures we'll be at the top of the precompilers...
        invade(app('blade.compiler'))->precompilers = [$compiler, ...invade(app('blade.compiler'))->precompilers];

        Blade::directive('renderSlot', function ($expression) {
            return <<<HTML
                <?php \Livewire\Mechanisms\ComponentDataStore::set(\$__livewire, 'slotProps', $expression); ?>
                |---SLOT---|
                HTML;
        });

        app('synthetic')->on('mount', function ($name, $params, $parent, $key, $slot) {
            if (! $slot) return;

            return function ($target) use ($slot) {
                ComponentDataStore::set($target, 'slot', $slot);
            };
        });

        app('synthetic')->on('dehydrate', function ($synth, $target, $context) {
            if (! $synth instanceof LivewireSynth) return;
            if (! ComponentDataStore::has($target, 'slot')) return;

            $slot = ComponentDataStore::get($target, 'slot');

            $context->addMeta('slot', $slot);

            return function ($value) use ($target, $context, $slot) {
                $html = $context->effects['html'];

                if (! $html) return $value;

                [$hash, $scope] = explode('----', $slot);
                $scopeVarNames = $scope ? explode(',', $scope) : [];

                $path = storage_path('framework/cache/livewire-'.$hash.'.blade.php');

                $slotProps = ComponentDataStore::get($target, 'slotProps', []);

                $slotContents = Blade::render(file_get_contents($path), array_intersect_key($slotProps, array_flip($scopeVarNames)));

                $context->effects['html'] = str($html)->replace('|---SLOT---|', $slotContents);

                return $value;
            };
        });

        app('synthetic')->on('hydrate', function ($synth, $rawValue, $meta) {
            if (! $synth instanceof LivewireSynth) return;
            if (! isset($meta['slot'])) return;

            $slot = $meta['slot'];

            return function ($target) use ($slot) {
                ComponentDataStore::set($target, 'slot', $slot);

                return $target;
            };
        });
    }
}
