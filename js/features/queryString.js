import { on } from '@synthetic/events'
import { dataGet, dataSet } from '@synthetic/utils'
import Alpine from 'alpinejs'
import { track } from '@alpinejs/history'

on('component.init', component => {
    let effects = component.synthetic.effects
    let queryString = effects['url']

    if (! queryString) return

    Object.entries(queryString).forEach(([key, value]) => {
        let { name, as, except, use, alwaysShow } = normalizeQueryStringEntry(key, value)

        let initialValue = dataGet(component.synthetic.ephemeral, name)

        let { initial, replace, push, pop } = track(as, initialValue, alwaysShow)

        if (use === 'replace') {
            Alpine.effect(() => {
                replace(dataGet(component.synthetic.reactive, name))
            })
        } else if (use === 'push') {
            on('target.request', (target, payload) => {
                if (target !== Alpine.raw(component.synthetic)) return // @todo: get rid of Alpine.raw by making .synthetic NOT a proxy

                return () => {
                    let diff = payload.diff
                    let dirty = target.effects.dirty || []

                    if (! Object.keys(payload.diff).includes(name) && ! dirty.some(i => i.startsWith(name))) return

                    push(dataGet(component.synthetic.ephemeral, name))
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
    let defaults = { except: null, use: 'replace', alwaysShow: false }

    if (typeof value === 'string') {
        return {...defaults, name: value, as: value }
    } else {
        let fullerDefaults = {...defaults, name: key, as: key }

        return {...fullerDefaults, ...value }
    }
}
