<?php

namespace Livewire\V4\Tailwind;

use TailwindMerge\TailwindMerge;

class Merge
{
    // @note: This should be swapped out with our own implementation so that we
    // have more control and can support Tailwind 4+...
    public function merge(string ...$classLists): string
    {
        $tw = TailwindMerge::instance();

        return $tw->merge(...$classLists);
    }
}
