@extends('layouts.app-for-normal-views')

@section('content')
    <div>
        @livewire(\Tests\Browser\DynamicComponentLoading\ClickableComponent::class)
    </div>
@endsection
