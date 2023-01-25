<div>
    <button wire:click="$refresh" dusk="refresh">Refresh</button>
    <button wire:click="flashMessage" dusk="flash">Flash</button>
    <button wire:click="redirectWithFlash" dusk="redirect-with-flash">Redirect With Flash</button>

    <button wire:click="redirectPage" dusk="redirect.button">Redirect Page</button>
    <span dusk="redirect.blade.output">{{ $message }}</span>
    <span x-data="{ message: @entangle('message') }" x-text="message" dusk="redirect.alpine.output"></span>

    <button wire:click="redirectPageWithModel" dusk="redirect-with-model.button">Redirect PageWithModel</button>
    <span dusk="redirect.blade.model-output">{{ $foo }}</span>
    <span x-data="{ message: @entangle('foo') }" x-text="message" dusk="redirect.alpine.model-output"></span>

    <div>
        @if (session()->has('message'))
            <h1 dusk="flash.message">{{ session()->get('message') }}</h1>
        @endif
    </div>
</div>
