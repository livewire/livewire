<div>
    <div>{{ $string }}</div>
    <div>{{ $number }}</div>
    <pre>{{ print_r($array, true) }}</pre>
    <pre>{{ print_r($recursiveArray, true) }}</pre>
    <pre>{{ print_r($collection->toArray(), true) }}</pre>
    <pre>{{ print_r($recursiveCollection->toArray(), true) }}</pre>
    <pre>{{ print_r($model->name, true) }}</pre>
    <pre>{{ print_r($modelCollection->toArray(), true) }}</pre>
    <button id="add_number" wire:click="addNumber">Add Number</button>
</div>
