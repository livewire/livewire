import { overrideMethod } from '@/$wire'
import { fireAction } from '@/request'
import { on } from '@/hooks'

on('component.init', ({ component }) => {
    if (! component.isLazy || component.hasBeenLazyLoaded) return

    let pending

    // The placeholder's `x-intersect` has no `.once` modifier, so it fires again every time
    // the element re-enters the viewport. Reuse the in-flight load rather than sending a
    // duplicate request, but forget it afterwards so a load that failed on a flaky
    // connection is still retried the next time the placeholder scrolls back in...
    overrideMethod(component, '__lazyLoad', (params) => {
        if (component.hasBeenLazyLoaded) return Promise.resolve()

        if (pending) return pending

        pending = fireAction(component, '__lazyLoad', params)

        pending.catch(() => {}).finally(() => pending = null)

        return pending
    })
})
