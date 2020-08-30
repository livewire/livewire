import store from '@/Store'

export default function () {
    store.registerHook('componentInitialized', component => {
        component.targetedLoadingElsByAction = {}
        component.genericLoadingEls = []
        component.currentlyActiveLoadingEls = []
        component.currentlyActiveUploadLoadingEls = []
    })

    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('loading')) return

        const loadingDirectives = el.directives.directives.filter(
            i => i.type === 'loading'
        )

        loadingDirectives.forEach(directive => {
            processLoadingDirective(component, el, directive)
        })
    })

    store.registerHook('messageSent', (component, message) => {
        const actions = message.updateQueue
            .filter(action => {
                return action.type === 'callMethod'
            })
            .map(action => action.payload.method)

        const models = message.updateQueue
            .filter(action => {
                return action.type === 'syncInput'
            })
            .map(action => action.payload.name)

        setLoading(component, actions.concat(models))
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

function processLoadingDirective(component, el, directive) {
    // If this element is going to be dealing with loading states.
    // We will initialize an "undo" stack upfront, so we don't
    // have to deal with isset() type conditionals later.
    el.el.__livewire_on_finish_loading = []

    var actionNames = false

    if (el.directives.get('target')) {
        // wire:target overrides any automatic loading scoping we do.
        actionNames = el.directives
            .get('target')
            .value.split(',')
            .map(s => s.trim())
    } else {
        // If there is no wire:target, let's check for the existance of a wire:click="foo" or something,
        // and automatically scope this loading directive to that action.
        const nonActionOrModelLivewireDirectives = [
            'init',
            'dirty',
            'offline',
            'target',
            'loading',
            'poll',
            'ignore',
            'key',
            'id',
        ]

        actionNames = el.directives
            .all()
            .filter(i => !nonActionOrModelLivewireDirectives.includes(i.type))
            .map(i => i.method)

        // If we found nothing, just set the loading directive to the global component. (run on every request)
        if (actionNames.length < 1) actionNames = false
    }

    addLoadingEl(component, el, directive, actionNames)
}

function addLoadingEl(component, el, directive, actionsNames) {
    if (actionsNames) {
        actionsNames.forEach(actionsName => {
            if (component.targetedLoadingElsByAction[actionsName]) {
                component.targetedLoadingElsByAction[actionsName].push({
                    el,
                    directive,
                })
            } else {
                component.targetedLoadingElsByAction[actionsName] = [
                    { el, directive },
                ]
            }
        })
    } else {
        component.genericLoadingEls.push({ el, directive })
    }
}

function removeLoadingEl(component, el) {
    // Look through the global/generic elements for the element to remove.
    component.genericLoadingEls.forEach((element, index) => {
        if (element.el.isSameNode(el)) {
            component.genericLoadingEls.splice(index, 1)
        }
    })

    // Look through the targeted elements to remove.
    Object.keys(component.targetedLoadingElsByAction).forEach(key => {
        component.targetedLoadingElsByAction[
            key
        ] = component.targetedLoadingElsByAction[key].filter(element => {
            return !element.el.isSameNode(el)
        })
    })
}

function setLoading(component, actions) {
    const actionTargetedEls = actions
        .map(action => component.targetedLoadingElsByAction[action])
        .filter(el => el)
        .flat()

    const allEls = component.genericLoadingEls.concat(actionTargetedEls)

    startLoading(allEls)

    component.currentlyActiveLoadingEls = allEls
}

export function setUploadLoading(component, modelName) {
    const actionTargetedEls =
        component.targetedLoadingElsByAction[modelName] || []

    const allEls = component.genericLoadingEls.concat(actionTargetedEls)

    startLoading(allEls)

    component.currentlyActiveUploadLoadingEls = allEls
}

export function unsetUploadLoading(component) {
    endLoading(component.currentlyActiveUploadLoadingEls)

    component.currentlyActiveUploadLoadingEls = []
}

function unsetLoading(component) {
    endLoading(component.currentlyActiveLoadingEls)

    component.currentlyActiveLoadingEls = []
}

function startLoading(els) {
    els.forEach(({ el, directive }) => {
        el = el.el // I'm so sorry.

        if (directive.modifiers.includes('class')) {
            let classes = directive.value.split(' ').filter(Boolean)

            doAndSetCallbackOnElToUndo(
                el,
                directive,
                () => el.classList.add(...classes),
                () => el.classList.remove(...classes)
            )
        } else if (directive.modifiers.includes('attr')) {
            doAndSetCallbackOnElToUndo(
                el,
                directive,
                () => el.setAttribute(directive.value, true),
                () => el.removeAttribute(directive.value)
            )
        } else {
            let cache = window
                .getComputedStyle(el, null)
                .getPropertyValue('display')

            doAndSetCallbackOnElToUndo(
                el,
                directive,
                () => {
                    el.style.display = directive.modifiers.includes('remove')
                        ? cache
                        : 'inline-block'
                },
                () => {
                    el.style.display = 'none'
                }
            )
        }
    })
}

function doAndSetCallbackOnElToUndo(el, directive, doCallback, undoCallback) {
    if (directive.modifiers.includes('remove'))
        [doCallback, undoCallback] = [undoCallback, doCallback]

    if (directive.modifiers.includes('delay')) {
        let timeout = setTimeout(() => {
            doCallback()
            el.__livewire_on_finish_loading.push(() => undoCallback())
        }, 200)

        el.__livewire_on_finish_loading.push(() => clearTimeout(timeout))
    } else {
        doCallback()
        el.__livewire_on_finish_loading.push(() => undoCallback())
    }
}

function endLoading(els) {
    els.forEach(({ el, directive }) => {
        el = el.el // I'm so sorry.

        while (el.__livewire_on_finish_loading.length > 0) {
            el.__livewire_on_finish_loading.shift()()
        }
    })
}
