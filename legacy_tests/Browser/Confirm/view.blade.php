<div>
    <form wire:submit="confirmAction" wire:confirm="please confirm">
        <input type="text" wire:model="confirmData" dusk="confirmInput">
        <button type="submit" dusk="confirmSubmit">Submit</button>
    </form>

    <form wire:submit="promptAction" wire:confirm.prompt="type PROMPT|PROMPT">
        <input type="text" wire:model="promptData" dusk="promptInput">
        <button type="submit" dusk="promptSubmit">Submit</button>
    </form>
</div>
