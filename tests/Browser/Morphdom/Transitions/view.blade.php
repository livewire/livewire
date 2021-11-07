<div>
    <input type="text" name="search" wire:model="search" />

    <ul>
        @foreach($this->names as $name)
            <li
                wire:key="{{ $name }}"
                name="names.{{ $name }}"
                wire:transition.enter="slide-up"
                wire:transition.leave="slide-down">
                {{ $name }}
            </li>
        @endforeach
    </ul>
</div>
