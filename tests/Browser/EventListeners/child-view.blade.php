<div>
    <hr />
    <h3>Child</h3>
    <div>
        <span dusk="child_eventCount">{{$eventCount}}</span><br />
        <span dusk="child_lastEvent">{{$lastEvent}}</span><br />

        <button dusk="child_removeBar" wire:click="delete(2)">Remove bar handler</button><br />
        <button dusk="child_removeBaz" wire:click="delete(3)">Remove bar handler</button><br />
        <button dusk="child_addGoo" wire:click="add(4, 'goo')">Add goo handler</button>
    </div>
</div>
