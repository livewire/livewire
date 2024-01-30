<div>
    @foreach ($items as $index => $item)
        <div wire:key="{{$item['id']}}" x-data="{show: false}">
            <div>{{ $item['title'] }}</div>

            <button dusk="complete-{{ $index }}" wire:click="complete({{ $index }})">complete</button>

            <div x-show="show" x-cloak dusk="hidden">
                Hidden area for {{ $item['title'] }}
            </div>
        </div>
    @endforeach
</div>
