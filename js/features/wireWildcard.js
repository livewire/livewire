import { directives } from '../directives'
import { on } from './../synthetic/index'
import Alpine from 'alpinejs'
import { callAndClearComponentDebounces } from 'debounce'

export default function () {
    on('element.init', (el, component) => {
        directives(el).all().forEach(directive => {
            if (['model', 'init', 'loading', 'poll', 'ignore', 'id', 'initial-data', 'key', 'target', 'dirty'].includes(directive.type)) return

            let attribute = directive.rawName.replace('wire:', 'x-on:')

            // Automatically add .prevent to wire:submit, if they didn't add it themselves...
            if (directive.type === 'submit' && ! directive.modifiers.includes('prevent')) {
                attribute = attribute + '.prevent'
            }

            Alpine.bind(el, {
                [attribute](e) {
                    // Forward these calls directly to $wire. Let them handle
                    // firing the request.

                    callAndClearComponentDebounces(component, () => {
                        Alpine.evaluate(el, '$wire.'+directive.value, { scope: { $event: e }})
                    })
                }
            })
        })
    })
}
