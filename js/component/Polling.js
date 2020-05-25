import MethodAction from '@/action/method'
import DOMElement from '@/dom/dom_element'
import store from '@/Store'

export default function () {
    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('poll')) return

        let intervalId = fireActionOnInterval(el.el, component)

        component.addListenerForTeardown(() => {
            clearInterval(intervalId)
        })

        el.el.__livewire_polling_interval = intervalId
    })

    store.registerHook('beforeElementUpdate', (from, to, component) => {
        if (from.__livewire_polling_interval !== undefined) return

        let fromEl = new DOMElement(from)
        let toEl = new DOMElement(to)

        if (fromEl.directives.missing('poll') && toEl.directives.has('poll')) {
            setTimeout(() => {
                let intervalId = fireActionOnInterval(fromEl.el, component)

                component.addListenerForTeardown(() => {
                    clearInterval(intervalId)
                })

                from.__livewire_polling_interval = intervalId
            }, 0)
        }
    })
}

function fireActionOnInterval(node, component) {
    let interval = (new DOMElement(node)).directives.get('poll').durationOr(2000);

    return setInterval(() => {
        if (node.isConnected === false) return

        let el = new DOMElement(node);

        // Don't poll when directive is removed from element.
        if (el.directives.missing('poll')) return

        const directive = el.directives.get('poll')
        const method = directive.method || '$refresh'

        // Don't poll when the tab is in the background.
        // The "Math.random" business effectivlly prevents 95% of requests
        // from executing. We still want "some" requests to get through.
        if (store.livewireIsInBackground && Math.random() < .95) return

        // Don't poll if livewire is offline as well.
        if (store.livewireIsOffline) return

        component.addAction(new MethodAction(method, directive.params, el))
    }, interval);
}
