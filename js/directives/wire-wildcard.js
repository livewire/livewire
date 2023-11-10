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
            let execute = () => {
                callAndClearComponentDebounces(component, () => {
                    // Forward these calls directly to $wire. Let them handle firing the request.
                    Alpine.evaluate(el, '$wire.'+directive.expression, { scope: { $event: e }})
                })
            }

            // Prevent form to be submitted
            if(e.type == 'submit' && e.target.hasAttribute('data-submitting')) {
                return;
            }

            // Account for the existence of wire:confirm="..." on the action...
            if (el.__livewire_confirm) {
                el.__livewire_confirm(() => {
                    execute()
                })
            } else {
                execute()
            }

            if(e.type == 'submit') {
                e.target.setAttribute('data-submitting', true);
            }
        }
    })

    cleanup(cleanupBinding)
})
