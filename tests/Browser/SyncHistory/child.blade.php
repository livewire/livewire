<div>
    <hr />
    <dt>DarkMode</dt>
    <dd>
        Dark mode is currently {{ $darkmode ? 'enabled' : 'disabled' }}.
        <button dusk="toggle-darkmode" wire:click="toggleDarkmode">Toggle</button>
    </dd>
</div>
