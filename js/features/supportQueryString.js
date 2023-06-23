import { on } from '@/events'
import { dataGet, dataSet } from '@/utils'
import Alpine from 'alpinejs'
import { track } from '@alpinejs/history'

on('component.init', component => {
    let effects = component.effects
    let queryString = effects['url']

    if (! queryString) return

    Object.entries(queryString).forEach(([key, value]) => {
        let { name, as, use, alwaysShow } = normalizeQueryStringEntry(key, value)

        let initialValue = dataGet(component.ephemeral, name)

        let { initial, replace, push, pop } = track(as, initialValue, alwaysShow)

        if (use === 'replace') {
            Alpine.effect(() => {
                replace(dataGet(component.reactive, name))
            })
        } else if (use === 'push') {
            on('request', (component, payload) => {
                let beforeValue = dataGet(component.ephemeral, name)

                return () => {
                    let afterValue = dataGet(component.ephemeral, name)
                    console.log(afterValue)

                    if (JSON.stringify(beforeValue) === JSON.stringify(afterValue)) return

                    // @todo: i went with the above json.stringify strategy, but left this here
                    // in case for some reason it was better... Remove it if all tests pass...
                    // let updates = payload.updates
                    // let dirty = component.effects.dirty || []
                    // if (! Object.keys(updates).includes(name) && ! dirty.some(i => i.startsWith(name))) return

                    push(afterValue)
                }
            })

            pop(async newValue => {
                await component.$wire.set(name, newValue)

                // @todo: this is the absolute worst thing ever I'm so sorry this needs to be refactored stat:
                document.querySelectorAll('input').forEach(el => {
                    el._x_forceModelUpdate && el._x_forceModelUpdate(el._x_model.get())
                })
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
