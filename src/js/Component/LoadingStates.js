import store from '@/Store'

export default function () {
    store.registerHook('componentInitialized', component => {
        component.loadingElsByRef = {}
        component.loadingEls = []
        component.currentlyActiveLoadingEls = []
    })

    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('loading')) return
        const directive = el.directives.get('loading')

        const refNames = el.directives.get('target')
            && el.directives.get('target').value.split(',').map(s => s.trim())

        addLoadingEl(
            component,
            el,
            directive.value,
            refNames,
            directive.modifiers.includes('remove')
        )
    })

    store.registerHook('messageSent', (component, message) => {
        setLoading(component, message.refs)
    })

    store.registerHook('messageFailed', component => {
        unsetLoading(component)
    })

    store.registerHook('responseReceived', component => {
        unsetLoading(component)
    })

    store.registerHook('elementRemoved', (el, component) => {
        removeLoadingEl(component, el)
    })
}

function addLoadingEl(component, el, value, targetNames, remove) {
    if (targetNames) {
        targetNames.forEach(targetNames => {
            if (component.loadingElsByRef[targetNames]) {
                component.loadingElsByRef[targetNames].push({el, value, remove})
            } else {
                component.loadingElsByRef[targetNames] = [{el, value, remove}]
            }
        })
    } else {
        component.loadingEls.push({el, value, remove})
    }
}

function removeLoadingEl(component, el) {
    component.loadingEls = component.loadingEls.filter(loadingEl => ! loadingEl.el.isSameNode(el))

    if (el.ref in component.loadingElsByRef) {
        delete component.loadingElsByRef[el.ref]
    }
}

function setLoading(component, refs) {
    const refEls = refs.map(ref => component.loadingElsByRef[ref]).filter(el => el).flat()

    const allEls = component.loadingEls.concat(refEls)

    allEls.forEach(el => {
        const directive = el.el.directives.get('loading')
        el = el.el.el // I'm so sorry @todo

        if (directive.modifiers.includes('class')) {
            // This is because wire:loading.class="border border-red"
            // wouldn't work with classList.add.
            const classes = directive.value.split(' ')

            if (directive.modifiers.includes('remove')) {
                el.classList.remove(...classes)
            } else {
                el.classList.add(...classes)
            }
        } else if (directive.modifiers.includes('attr')) {
            if (directive.modifiers.includes('remove')) {
                el.removeAttribute(directive.value)
            } else {
                el.setAttribute(directive.value, true)
            }
        } else {
            el.style.display = 'inline-block'
        }
    })

    component.currentlyActiveLoadingEls = allEls
}

function unsetLoading(component) {
    component.currentlyActiveLoadingEls.forEach(el => {
        const directive = el.el.directives.get('loading')
        el = el.el.el // I'm so sorry @todo

        if (directive.modifiers.includes('class')) {
            const classes = directive.value.split(' ')

            if (directive.modifiers.includes('remove')) {
                el.classList.add(...classes)
            } else {
                el.classList.remove(...classes)
            }
        } else if (directive.modifiers.includes('attr')) {
            if (directive.modifiers.includes('remove')) {
                el.setAttribute(directive.value)
            } else {
                el.removeAttribute(directive.value, true)
            }
        } else {
            el.style.display = 'none'
        }
    })

    component.currentlyActiveLoadingEls = []
}
