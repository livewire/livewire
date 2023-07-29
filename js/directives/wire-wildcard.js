import { callAndClearComponentDebounces } from '@/debounce'
import { getDirectives } from '@/directives'
import { on } from '@/events'
import Alpine from 'alpinejs'

on('directive.init', ({ el, directive, cleanup, component }) => {
    if (['snapshot', 'effects', 'model', 'init', 'loading', 'poll', 'ignore', 'id', 'data', 'key', 'target', 'dirty'].includes(directive.value)) return

    let attribute = directive.rawName.replace('wire:', 'x-on:')

    // Automatically add .prevent to wire:submit, if they didn't add it themselves...
    if (directive.value === 'submit' && ! directive.modifiers.includes('prevent')) {
        attribute = attribute + '.prevent'
    }

    let cleanupBinding = Alpine.bind(el, {
        [attribute](e) {
            callAndClearComponentDebounces(component, () => {
                // Forward these calls directly to $wire. Let them handle firing the request.
                Alpine.evaluate(el, '$wire.'+directive.expression, { scope: { $event: e }})
            })
        }
    })

    cleanup(cleanupBinding)
})
