import { directives } from '../directives'
import { on } from '@synthetic/index'

export default function () {
    on('element.init', (el, component) => {
        directives(el).all().forEach(directive => {
            if (['model', 'init', 'loading', 'poll', 'ignore', 'id', 'initial-data', 'key', 'target', 'dirty'].includes(directive.type)) return

            let attribute = directive.rawName.replace('wire:', 'x-on:')

            Alpine.bind(el, {
                [attribute](e) {
                    // Forward these calls directly to $wire. Let them handle
                    // firing the request.
                    Alpine.evaluate(el, '$wire.'+directive.value, { scope: { $event: e }})
                }
            })
        })
    })
}
