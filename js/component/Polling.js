import MethodAction from '@/action/method'
import store from '@/Store'

export default function () {
    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('poll')) return

        fireActionOnInterval(el, component)
    })
}

function fireActionOnInterval(el, component) {
    const directive = el.directives.get('poll')
    const method = directive.method || '$refresh'

    setInterval(() => {
        // Don't poll when the tab is in the background.
        // The "Math.random" business effectivlly prevents 95% of requests
        // from executing. We still want "some" requests to get through.
        if (store.livewireIsInBackground && Math.random() < .95) return

        // Don't poll if livewire is offline as well.
        if (store.livewireIsOffline) return

        component.addAction(new MethodAction(method, directive.params, el))
    }, directive.durationOr(2000));
}
