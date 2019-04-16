import Livewire from 'laravel-livewire'

export function mount(dom, requestInterceptor = () => {}) {
    document.body.innerHTML = '<div wire:id="123" wire:serialized="{&quot;properties&quot;: {}}">' + dom + '</div>'

    document.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            requestInterceptor(payload)
        },
    }})

    return document.body.firstElementChild
};

export function mountWithEvent(dom, event, requestInterceptor = () => {}) {
    document.body.innerHTML = '<div wire:id="123" wire:listening-for="[&quot;'+event+'&quot;]" wire:serialized="{&quot;properties&quot;: {}}">' + dom + '</div>'

    document.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            requestInterceptor(payload)
        },
    }})

    return document.body.firstElementChild
};

export function mountAndReturn(dom, returnedDom, dirtyInputs = []) {
    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';

    document.body.innerHTML = '<div wire:id="123" wire:serialized="{&quot;properties&quot;: {}}">' + dom + '</div>'

    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            this.onMessage({
                id: '123',
                serialized: '{"properties": {}}',
                dirtyInputs: dirtyInputs,
                dom: '<div wire:id="123" wire:serialized="{&quot;properties&quot;: {}}">' + returnedDom + '</div>',
            })
        },
    }})

    return document.body.firstElementChild
};

export function mountAndReturnWithData(dom, returnedDom, data) {
    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';

    document.body.innerHTML = '<div wire:id="123" wire:serialized="{&quot;properties&quot;: {}}">' + dom + '</div>'

    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            this.onMessage({
                id: '123',
                serialized: JSON.stringify({ properties: data }),
                dirtyInputs: {},
                dom: '<div wire:id="123" wire:serialized="{&quot;properties&quot;: {}}">' + returnedDom + '</div>',
            })
        },
    }})

    return document.body.firstElementChild
};
