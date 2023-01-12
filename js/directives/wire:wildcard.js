import { callAndClearComponentDebounces } from '@/debounce'
import { getDirectives } from '@/directives'
import { on } from '@synthetic/index'
import Alpine from 'alpinejs'

on('element.init', (el, component) => {
    getDirectives(el).all().forEach(directive => {
        if (['model', 'init', 'loading', 'poll', 'ignore', 'id', 'initial-data', 'key', 'target', 'dirty'].includes(directive.type)) return

        let attribute = directive.rawName.replace('wire:', 'x-on:')

        // Automatically add .prevent to wire:submit, if they didn't add it themselves...
        if (directive.value === 'submit' && ! directive.modifiers.includes('prevent')) {
            attribute = attribute + '.prevent'
        }

        Alpine.bind(el, {
            [attribute](e) {
                callAndClearComponentDebounces(component, () => {
                    // Forward these calls directly to $wire. Let them handle firing the request.
                    Alpine.evaluate(el, '$wire.'+directive.expression, { scope: { $event: e }})
                })
            }
        })
    })
})
