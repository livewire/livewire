<div dusk="root">
    <h1>Result</h1>
    <div class="result" id="result1" dusk="result1">TBD</div>
    <div class="result" id="result2" dusk="result2">TBD</div>

    <button wire:click="click('#result1', 'Clicked1')" dusk="btn1">Click 1</button>
    <button wire:click="click('#result2', 'Clicked2')" dusk="btn2">Click 2</button>
    <button wire:click="clickBoth()" dusk="btnBoth">Both</button>
    <button wire:click="click('.result', 'All')" dusk="btnAll">All</button>
</div>
