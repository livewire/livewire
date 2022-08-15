export class Component {
    constructor(synthetic, el, id) {
        this.synthetic = synthetic
        this.$wire = this.synthetic.reactive
        this.el = el
        this.id = id

        // So we can get Livewire components back from synthetic hooks.
        synthetic.__livewireId = this.id
    }
}

