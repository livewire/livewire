import { dispatch, dispatchEl, dispatchRef, dispatchSelf, dispatchTo } from '@/events'
import { on } from '@/hooks'
import { interceptMessage } from '@/request'

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorphed }) => {
        onMorphed(() => {
            dispatchEvents(message.component, getDispatches(payload.effects))
        })
    })
})

on('component.initialized', ({ component }) => {
    let dispatches = getDispatches(component.effects)

    if (dispatches.length === 0) return

    // Wrapping initial dispatches in a triple queueMicrotask...
    // The first one puts them after all initialization and "effect" hooks...
    // The second one puts them after all reactive Alpine effects
    // (that are processed via flushJobs in scheduler)...
    // The third one puts them after DOM initialization changes have been applied...
    queueMicrotask(() => {
        queueMicrotask(() => {
            queueMicrotask(() => {
                dispatchEvents(component, dispatches)
            })
        })
    })
})

function getDispatches(effects) {
    return effects.dispatches || []
}

function dispatchEvents(component, dispatches) {
    dispatches.forEach(({ name, params = {}, self = false, component: componentName, ref, el }) => {
        if (self) dispatchSelf(component, name, params)
        else if (componentName) dispatchTo(componentName, name, params)
        else if (ref) dispatchRef(component, ref, name, params)
        else if (el) dispatchEl(component, el, name, params)
        else dispatch(component, name, params)
    })
}
