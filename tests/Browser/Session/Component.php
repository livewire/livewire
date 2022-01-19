<?php

namespace Tests\Browser\Session;

use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public $useCustomPageExpiredHook = false;

    protected $queryString = [
        'useCustomPageExpiredHook' => ['except' => false],
    ];

    public function regenerateSession()
    {
        request()->session()->regenerate();
    }

    public function render()
    {
        return <<< 'HTML'
<div>
    <button type="button" wire:click="regenerateSession" dusk="regenerateSession">Regenerate Session</button>
    <button type="button" wire:click="$refresh" dusk="refresh">Refresh</button>

    @if($useCustomPageExpiredHook)
    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.onPageExpired(() => confirm('Page Expired'))
        })
    </script>
    @endif
</div>
HTML;
    }
}
