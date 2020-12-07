<div dusk="root">
    <h1>Result</h1>
    <div id="result1" dusk="result1">TBD</div>
    <div id="result2" dusk="result2">TBD</div>

    <button wire:click="click('#result1', 'Clicked')" dusk="btn1">Click 1</button>
    <button wire:click="click('#result2', 'Clicked')" dusk="btn2">Click 2</button>
    <button wire:click="clickBoth()" dusk="btn3">Both</button>
</div>
