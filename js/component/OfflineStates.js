import store from '@/Store'

var offlineEls = [];

export default function () {
    store.registerHook('elementInitialized', el => {
        if (el.directives.missing('offline')) return

        offlineEls.push(el)
    })

    window.addEventListener('offline', () => {
        store.livewireIsOffline = true

        offlineEls.forEach(el => {
            toggleOffline(el, true)
        })
    })

    window.addEventListener('online', () => {
        store.livewireIsOffline = false

        offlineEls.forEach(el => {
            toggleOffline(el, false)
        })
    })

    store.registerHook('elementRemoved', el => {
        offlineEls = offlineEls.filter(el => ! el.isSameNode(el))
    })
}

function toggleOffline(el, isOffline) {
    const directive = el.directives.get('offline')

    if (directive.modifiers.includes('class')) {
        const classes = directive.value.split(' ')
        if (directive.modifiers.includes('remove') !== isOffline) {
            el.rawNode().classList.add(...classes)
        } else {
            el.rawNode().classList.remove(...classes)
        }
    } else if (directive.modifiers.includes('attr')) {
        if (directive.modifiers.includes('remove') !== isOffline) {
            el.rawNode().setAttribute(directive.value, true)
        } else {
            el.rawNode().removeAttribute(directive.value)
        }
    } else if (! el.directives.get('model')) {
        el.rawNode().style.display = isOffline ? 'inline-block' : 'none'
    }
}
