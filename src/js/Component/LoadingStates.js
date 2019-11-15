import store from '@/Store'

export default function () {
    store.registerHook('componentInitialized', component => {
        component.targetedLoadingElsByAction = {}
        component.genericLoadingEls = []
        component.currentlyActiveLoadingEls = []
    })

    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('loading')) return
        const directive = el.directives.get('loading')

        const actionNames = el.directives.get('target')
            && el.directives.get('target').value.split(',').map(s => s.trim())

        addLoadingEl(
            component,
            el,
            directive.value,
            actionNames,
            directive.modifiers.includes('remove')
        )
    })

    store.registerHook('messageSent', (component, message) => {
        const actions = message.actionQueue.filter(action => {
            return action.type === 'callMethod'
        }).map(action => action.payload.method);

        setLoading(component, actions)
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

function addLoadingEl(component, el, value, actionsNames, remove) {
    if (actionsNames) {
        actionsNames.forEach(actionsName => {
            if (component.targetedLoadingElsByAction[actionsName]) {
                component.targetedLoadingElsByAction[actionsName].push({el, value, remove})
            } else {
                component.targetedLoadingElsByAction[actionsName] = [{el, value, remove}]
            }
        })
    } else {
        component.genericLoadingEls.push({el, value, remove})
    }
}

function removeLoadingEl(component, el) {
    component.genericLoadingEls = component.genericLoadingEls.filter(loadingEl => ! loadingEl.el.isSameNode(el))
}

function setLoading(component, actions) {
    const actionTargetedEls = actions.map(action => component.targetedLoadingElsByAction[action]).filter(el => el).flat()

    const allEls = component.genericLoadingEls.concat(actionTargetedEls)

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
