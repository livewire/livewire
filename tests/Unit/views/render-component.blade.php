<div>
    @isset($params)
        @livewire($component, $params)
    @else
        @livewire($component)
    @endisset
</div>
