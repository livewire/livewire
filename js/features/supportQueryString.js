import { on } from '@/hooks'
import { dataGet } from '@/utils'
import { dehydrateTree } from '@/synths'
import Alpine from 'alpinejs'
import { track } from '@/plugins/history'

on('effect', ({ component, effects, cleanup }) => {
    let queryString = effects['url']

    if (! queryString) return

    Object.entries(queryString).forEach(([key, value]) => {
        let { name, as, use, alwaysShow, except } = normalizeQueryStringEntry(key, value)

        if (! as) as = name

        // Rich synth values can't be serialized into a URL, so they're
        // converted back to their raw wire format at this boundary...
        let initialValue = [false, null, undefined].includes(except) ? dehydrateTree(dataGet(component.ephemeral, name)) : except

        let { replace, push, pop } = track(as, initialValue, alwaysShow, except)

        if (use === 'replace') {
            let effectReference = Alpine.effect(() => {
                replace(dehydrateTree(dataGet(component.reactive, name)))
            })

            cleanup(() => Alpine.release(effectReference))
        } else if (use === 'push') {
            let popNavigating = false

            let forgetCommitHandler = on('commit', ({ component: commitComponent, succeed }) => {
                if (component !== commitComponent) return

                let beforeValue = dehydrateTree(dataGet(component.canonical, name))

                succeed(() => {
                    let afterValue = dehydrateTree(dataGet(component.canonical, name))

                    if (JSON.stringify(beforeValue) === JSON.stringify(afterValue)) return

                    // If we're handling a popstate (back/forward navigation), use
                    // replaceState instead of pushState so we don't wipe out the
                    // forward history entries...
                    if (popNavigating) {
                        replace(afterValue)
                    } else {
                        push(afterValue)
                    }
                })
            })

            let forgetPopHandler = pop(async newValue => {
                popNavigating = true

                await component.$wire.set(name, newValue)

                // @todo: this is the absolute worst thing ever I'm so sorry this needs to be refactored stat:
                document.querySelectorAll('input').forEach(el => {
                    el._x_forceModelUpdate && el._x_forceModelUpdate(el._x_model.get())
                })

                // Use requestAnimationFrame to ensure the flag stays true through
                // the succeed callback which fires in a requestAnimationFrame...
                requestAnimationFrame(() => popNavigating = false)
            })

            // If the current property value differs from the initial value
            // (e.g. restored from session), sync the URL via replaceState...
            let currentValue = dehydrateTree(dataGet(component.ephemeral, name))

            if (JSON.stringify(currentValue) !== JSON.stringify(initialValue)) {
                replace(currentValue)
            }

            cleanup(() => {
                forgetCommitHandler()
                forgetPopHandler()
            })
        }
    })
})

function normalizeQueryStringEntry(key, value) {
    let defaults = { use: 'replace', alwaysShow: false }

    if (typeof value === 'string') {
        return {...defaults, name: value, as: value }
    } else {
        let fullerDefaults = {...defaults, name: key, as: key }

        return {...fullerDefaults, ...value }
    }
}
