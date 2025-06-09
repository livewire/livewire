@layout('layouts::app', ['foo' => 'bar'])

@php
new class extends \Livewire\Component {
    //
}
@endphp

<div>
    Dashboard
</div>

<script>
    console.log(this);
</script>