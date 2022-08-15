import { directives } from '../directives'
import { on } from '../events'

export default function () {
    on('element.init', (el) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('target')) return

        let directive = allDirectives.get('target')

        el._lw_isTargeting = names => {
            if (typeof names === 'string') names = [names]

            let actionNames = []
            if (directive.params.length > 0) {
                actionNames = [
                    generateSignatureFromMethodAndParams(
                        directive.method,
                        directive.params
                    ),
                ]
            } else {
                // wire:target overrides any automatic loading scoping we do.
                actionNames = directive.value.split(',').map(s => s.trim())
            }

            return actionNames.some(i => names.includes(i))
        }
    })
}

function generateSignatureFromMethodAndParams(method, params) {
    return method + btoa(encodeURIComponent(params.toString()))
}
