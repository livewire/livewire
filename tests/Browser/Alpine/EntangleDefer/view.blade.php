<div x-data="{ testing:  @entangle('testing').defer }">
    <input type="text" x-model="testing" dusk="input">

    <p>Alpine: <span dusk="output.alpine" x-text="testing"></span></p>

    <p>Livewire: <span dusk="output.livewire">{{$testing}}</span></p>
    
    <button wire:click.prevent="submit" dusk="submit">Submit</button>
</div>