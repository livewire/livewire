import { dispatch, dispatchEl, dispatchRef, dispatchSelf, dispatchTo } from '@/events'
import { on } from '@/hooks'
import { interceptMessage } from '@/request'

// Dispatches coming back from a server round-trip are anchored to the message's own
// post-morph hook instead of a detached queueMicrotask chain. `Message.invokeOnMorph()`
// awaits each interceptor's `onMorph()` serially, in registration order, and this file
// is imported AFTER `supportIslands` and `supportMorphDom` in `features/index.js`, so by
// the time this callback runs the new HTML has already been morphed into the DOM —
// post-morph, but still pre-paint.
//
// The ordering is load-bearing for two reasons: element-targeted dispatches (`->el()` /
// `->ref()`) resolve their target by querying the component's element at dispatch time,
// and even a bubbling `dispatch()` only reaches listeners that are already bound — both
// fail silently for an element this same update rendered if the event fires before the
// morph.
//
// Note the converse: `supportSlots` is imported later still, so its slot-fragment morphs
// have not yet run when this fires — events targeting elements inside a freshly rendered
// slot are still subject to the original race.
interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(() => {
            dispatchEvents(message.component, getDispatches(payload.effects))
        })
    })
})

// Effects that don't originate from a message never reach the interceptor above — the
// only such case is the initial page load, where `store.js` calls `processEffects()`
// without a request. Those mount-time dispatches still go out from here.
//
// Wrapping this in a triple queueMicrotask...
// The first one puts it after all other "effect" hooks...
// The second one puts it after all reactive Alpine effects
// (that are processed via flushJobs in scheduler)...
// The third one puts it after morph changes have been applied...
on('effect', ({ component, effects, request }) => {
    if (request) return

    queueMicrotask(() => {
        queueMicrotask(() => {
            queueMicrotask(() => {
                dispatchEvents(component, getDispatches(effects))
            })
        })
    })
})

function getDispatches(effects) {
    if (! Object.prototype.hasOwnProperty.call(effects, 'dispatches')) return []

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
