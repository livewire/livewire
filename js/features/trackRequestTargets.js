import { on } from "../events";

let componentTargets = {}

on('component.request', (component, payload) => {
    componentTargets[component.id] = getTargetsFromPayload(payload)
})

on('component.response', (component) => {
    delete componentTargets[component.id]
})

export function componentHasTargets(component, targetDirective) {
    let actionNames = []
    if (targetDirective.params.length > 0) {
        actionNames = [
            generateSignatureFromMethodAndParams(
                targetDirective.method,
                targetDirective.params
            ),
        ]
    } else {
        // wire:target overrides any automatic loading scoping we do.
        actionNames = targetDirective.value.split(',').map(s => s.trim())
    }

    return actionNames.some(i => componentTargets[component.id].includes(i))
}

function getTargetsFromPayload(payload) {
    let actions = payload.updates
        .filter(action => {
            return action.type === 'callMethod'
        })
        .map(action => action.payload.method)

    let actionsWithParams = payload.updates
        .filter(action => {
            return action.type === 'callMethod'
        })
        .map(action =>
            generateSignatureFromMethodAndParams(
                action.payload.method,
                action.payload.params
            )
        )

    let models = payload.updates
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

        return actions.concat(actionsWithParams).concat(models)
}

function generateSignatureFromMethodAndParams(method, params) {
    return method + btoa(encodeURIComponent(params.toString()))
}
