import { dispatch, dispatchSelf, dispatchTo } from '@/events'
import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    dispatchEvents(component, effects.dispatches || [])
})

function dispatchEvents(component, dispatches) {
    dispatches.forEach(({ name, params = {}, self = false, to }) => {
        if (self) dispatchSelf(component, name, params)
        else if (to) dispatchTo(to, name, params)
        else dispatch(component, name, params)
    })
}


