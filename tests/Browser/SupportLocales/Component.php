<?php

namespace Tests\Browser\SupportLocales;

use Illuminate\Support\Facades\App;
use Livewire\Component as BaseComponent;
use function Livewire\str;

class Component extends BaseComponent
{
    public $count = 0;

    public function boot()
    {
        /**
         * Set the app locale from prefix if it matches a predefined locale.
         * This simulates how localisation packages detect the prefix.
         */ 
        if (str(request()->path())->startsWith('de/') ) {
            App::setLocale('de');
        }
    }

    public function increaseCount()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
<div>
    <div>
        Locale: <span dusk="locale">{{ App::getLocale() }}</span>.
    </div>

    <div>
        Count: <span dusk="count">{{ $count }}</span>
    </div>

    <button type="button" wire:click="increaseCount" dusk="increaseCount">
        increaseCount()
    </button>
</div>

HTML;
    }
}
