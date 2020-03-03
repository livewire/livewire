import MethodAction from '@/action/method'
import store from '@/Store'

let defaultDuration = 2000

export default function () {
    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('poll')) return

        const directive = el.directives.get('poll')
        const method = directive.method || '$refresh'

        if (directive.modifiers.includes('on-minute')) {
          defaultDuration = 60000

          fireActionOnMinute(el, component, directive, method)
        } else {
          fireActionOnInterval(el, component, directive, method)
        }
    })
}

function fireActionOnInterval(el, component, directive, method) {
    setInterval(() => {
        // Don't poll when the tab is in the background.
        // The "Math.random" business effectively prevents 95% of requests
        // from executing. We still want "some" requests to get through.
        if (store.livewireIsInBackground && Math.random() < .95) return

        // Don't poll if livewire is offline as well.
        if (store.livewireIsOffline) return

        component.addAction(new MethodAction(method, directive.params, el))
    }, directive.durationOr(defaultDuration))
}

function fireActionOnMinute(el, component, directive, method) {
    const msUntilMinute = (60 - (new Date).getSeconds()) * 1000

    setTimeout(() => {
        component.addAction(new MethodAction(method, directive.params, el))

        fireActionOnInterval(el, component, directive, method)
    }, msUntilMinute)
}
