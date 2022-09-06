import { findComponent, state } from "../../../js/state";
import { directives } from "../../../js/directives";
import { on } from '../../../../synthetic/js/index'
import { morph } from "../../../js/morph";

export default function (enabled) {
    if (! enabled.includes('eager-loading')) return

    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('eager')) return

        let directive = allDirectives.get('eager')


    })

    on('effects', (target, effects, path) => {
        queueMicrotask(() => {
            let eager = effects.eager

            if (! eager) return

            let component = findComponent(target.__livewireId)

            component.__eager = eager
        })
    })

    on('target.request', (target, payload) => {
        let component = findComponent(target.__livewireId)

        if (! component.__eager) return

        let eager = component.__eager

        payload.calls.forEach(call => {
            eager.forEach(({ key, method, partial }) => {
                if (call.method === method) {
                   let el = component.el.querySelector('[wire\\:eager="'+key+'"]')
                   if (! el) throw 'Cant find eager element with key: '+key

                   morph(component, el, partial)
                }
            })
        })
    })
}
