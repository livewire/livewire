import { dispatch, dispatchSelf, dispatchTo } from '@/events'
import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    // Wrapping this in a triple queueMicrotask...
    // The first one puts it after all other "effect" hooks...
    // The second one puts it after all reactive Alpine effects
    // (that are processed via flushJobs in scheduler)...
    // The third one puts it after morph changes have been applied...
    queueMicrotask(() => {
        queueMicrotask(() => {
            queueMicrotask(() => {
                dispatchEvents(component, effects.dispatches || [])
            })
        })
    })
})

function dispatchEvents(component, dispatches) {
    dispatches.forEach(({ name, params = {}, self = false, to }) => {
        if (self) dispatchSelf(component, name, params)
        else if (to) dispatchTo(to, name, params)
        else dispatch(component, name, params)
    })
}


