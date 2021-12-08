import store from '@/Store'
import { wireDirectives } from '@/util'

export default function () {
    store.registerHook('component.initialized', component => {
        component.targetedLoadingElsByAction = {}
        component.genericLoadingEls = []
        component.currentlyActiveLoadingEls = []
        component.currentlyActiveUploadLoadingEls = []
    })

    store.registerHook('element.initialized', (el, component) => {
        let directives = wireDirectives(el)

        if (directives.missing('loading')) return

        const loadingDirectives = directives.directives.filter(
            i => i.type === 'loading'
        )

        loadingDirectives.forEach(directive => {
            processLoadingDirective(component, el, directive)
        })
    })

    store.registerHook('message.sent', (message, component) => {
        const actions = message.updateQueue
            .filter(action => {
                return action.type === 'callMethod'
            })
            .map(action => action.payload.method)

        const actionsWithParams = message.updateQueue
            .filter(action => {
                return action.type === 'callMethod'
            })
            .map(action =>
                generateSignatureFromMethodAndParams(
                    action.payload.method,
                    action.payload.params
                )
            )

        const models = message.updateQueue
            .filter(action => {
                return action.type === 'syncInput'
            })
            .map(action => {
                let name = action.payload.name
                if (! name.includes('.')) {
                    return name
                }

                let modelActions = []

                modelActions.push(
                    name.split('.').reduce((fullAction, part) => {
                        modelActions.push(fullAction)

                        return fullAction + '.' + part
                    })
                )

                return modelActions
            })
            .flat()

        setLoading(component, actions.concat(actionsWithParams).concat(models))
    })

    store.registerHook('message.failed', (message, component) => {
        unsetLoading(component)
    })

    store.registerHook('message.received', (message, component) => {
        unsetLoading(component)
    })

    store.registerHook('element.removed', (el, component) => {
        removeLoadingEl(component, el)
    })
}

function processLoadingDirective(component, el, directive) {
    // If this element is going to be dealing with loading states.
    // We will initialize an "undo" stack upfront, so we don't
    // have to deal with isset() type conditionals later.
    el.__livewire_on_finish_loading = []

    var actionNames = false

    let directives = wireDirectives(el)

    if (directives.get('target')) {
        let target = directives.get('target')
        if (target.params.length > 0) {
            actionNames = [
                generateSignatureFromMethodAndParams(
                    target.method,
                    target.params
                ),
            ]
        } else {
            // wire:target overrides any automatic loading scoping we do.
            actionNames = target.value.split(',').map(s => s.trim())
        }
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

        actionNames = directives
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
            return ! element.el.isSameNode(el)
        })
    })
}

function setLoading(component, actions) {
    const actionTargetedEls = actions
        .map(action => component.targetedLoadingElsByAction[action])
        .filter(el => el)
        .flat()

    const allEls = removeDuplicates(component.genericLoadingEls.concat(actionTargetedEls))

    startLoading(allEls)

    component.currentlyActiveLoadingEls = allEls
}

export function setUploadLoading(component, modelName) {
    const actionTargetedEls =
        component.targetedLoadingElsByAction[modelName] || []

    const allEls = removeDuplicates(component.genericLoadingEls.concat(actionTargetedEls))

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
                        : getDisplayProperty(directive)
                },
                () => {
                    el.style.display = 'none'
                }
            )
        }
    })
}

function getDisplayProperty(directive) {
    return (['inline', 'block', 'table', 'flex', 'grid', 'inline-flex']
        .filter(i => directive.modifiers.includes(i))[0] || 'inline-block')
}

function doAndSetCallbackOnElToUndo(el, directive, doCallback, undoCallback) {
    if (directive.modifiers.includes('remove'))
        [doCallback, undoCallback] = [undoCallback, doCallback]

    if (directive.modifiers.includes('delay')) {
        let duration = 200

        let delayModifiers = {
            'shortest': 50,
            'shorter': 100,
            'short': 150,
            'long': 300,
            'longer': 500,
            'longest': 1000,
        }

        Object.keys(delayModifiers).some(key => {
            if(directive.modifiers.includes(key)) {
                duration = delayModifiers[key]
                return true
            }
        })

        let timeout = setTimeout(() => {
            doCallback()
            el.__livewire_on_finish_loading.push(() => undoCallback())
        }, duration)

        el.__livewire_on_finish_loading.push(() => clearTimeout(timeout))
    } else {
        doCallback()
        el.__livewire_on_finish_loading.push(() => undoCallback())
    }
}

function endLoading(els) {
    els.forEach(({ el }) => {
        while (el.__livewire_on_finish_loading.length > 0) {
            el.__livewire_on_finish_loading.shift()()
        }
    })
}

function generateSignatureFromMethodAndParams(method, params) {
    return method + btoa(encodeURIComponent(params.toString()))
}

function removeDuplicates(arr) {
    return Array.from(new Set(arr))
}