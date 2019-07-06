import Livewire from 'laravel-livewire'

export function mount(dom, requestInterceptor = () => {}) {
    document.body.innerHTML = '<div wire:id="123" wire:data="{}">' + dom + '</div>'

    new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            requestInterceptor(payload)
        },
    }})

    return document.body.firstElementChild
};

export function mountWithEvent(dom, event, requestInterceptor = () => {}) {
    document.body.innerHTML = '<div wire:id="123" wire:events="[&quot;'+event+'&quot;]" wire:data="{}">' + dom + '</div>'

    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            requestInterceptor(payload)
        },
    }})

    return document.body.firstElementChild
};

export function mountAndReturn(dom, returnedDom, dirtyInputs = [], requestInterceptor = () => { }) {
    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';

    document.body.innerHTML = '<div wire:id="123" wire:data="{}">' + dom + '</div>'

    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            requestInterceptor(payload)
            this.onMessage({
                id: payload.id,
                data: {},
                dirtyInputs: dirtyInputs,
                dom: '<div wire:id="123" wire:data="{}">' + returnedDom + '</div>',
            })
        },
    }})

    return document.body.firstElementChild
};

export function mountWithData(dom, data) {
    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';
    document.body.innerHTML = '<div wire:id="123" wire:data="' + JSON.stringify(data).replace(/\"/g, '&quot;')+'">' + dom + '</div>'

    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            //
        },
    }})

    return document.body.firstElementChild
};

export function mountAndReturnWithData(dom, returnedDom, data, dirtyInputs = []) {
    // This is a crude way of wiping any existing DOM & listeners before we mount.
    document.body.innerHTML = '';
    window.livewire && window.livewire.restart()

    document.body.innerHTML = '<div wire:id="123" wire:data="{}">' + dom + '</div>'

    window.livewire = new Livewire({ driver: {
        onMessage: null,
        init() {},
        sendMessage(payload) {
            this.onMessage({
                id: payload.id,
                data,
                dirtyInputs,
                dom: '<div wire:id="123" wire:data="{}">' + returnedDom + '</div>',
            })
        },
    }})

    return document.body.firstElementChild
};
