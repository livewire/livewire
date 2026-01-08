@php
new class extends Livewire\Component {
    public function save()
    {
        //

        $this->dispatch('close')->to(ref: 'modal');
    }
}
@endphp

<div>
    <livewire:modal wire:ref="modal">
        <form>
            <button wire:click="save">Save</button>

            <button wire:click="$refs.modal.close()">Save</button>
        </form>
    </livewire:modal>
</div>

<script>
this.foo = async () => {
    await this.save();

    this.$refs.modal.close();
}
</script>