import Livewire from 'laravel-livewire'

export function mount(dom, requestInterceptor = () => {}) {
    document.body.innerHTML = '<div wire:id="123" wire:serialized="noop">' + dom + '</div>'

    new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            requestInterceptor(payload)
        },
    }})

    return document.body.firstElementChild
};

export function mountAndReturn(dom, returnedDom) {
    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';

    document.body.innerHTML = '<div wire:id="123" wire:serialized="noop">' + dom + '</div>'

    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            this.onMessage({
                id: '123',
                serialized: 'noop',
                dirtyInputs: [],
                dom: '<div wire:id="123" wire:serialized="noop">' + returnedDom + '</div>',
            })
        },
    }})

    return document.body.firstElementChild
};
