import { callAndClearComponentDebounces } from '@/debounce'
import { customDirectiveHasBeenRegistered } from '@/directives'
import { on } from '@/hooks'
import Alpine from 'alpinejs'
import interceptorRegistry from '@/v4/interceptors/interceptorRegistry.js'

on('directive.init', ({ el, directive, cleanup, component }) => {
    if (['snapshot', 'effects', 'model', 'init', 'loading', 'poll', 'ignore', 'id', 'data', 'key', 'target', 'dirty'].includes(directive.value)) return
    if (customDirectiveHasBeenRegistered(directive.value)) return

    let attribute = directive.rawName.replace('wire:', 'x-on:')

    // Automatically add .prevent to wire:submit, if they didn't add it themselves...
    if (directive.value === 'submit' && ! directive.modifiers.includes('prevent')) {
        attribute = attribute + '.prevent'
    }

    let cleanupBinding = Alpine.bind(el, {
        [attribute](e) {
            let execute = () => {
                callAndClearComponentDebounces(component, () => {
                    interceptorRegistry.fire(el, directive, component)

                    Alpine.evaluate(el, 'await $wire.'+directive.expression, { scope: { $event: e }})
                })
            }

            // Account for the existance of wire:confirm="..." on the action...
            if (el.__livewire_confirm) {
                el.__livewire_confirm(() => {
                    execute()
                }, () => {
                    e.stopImmediatePropagation()
                })
            } else {
                execute()
            }
        }
    })

    cleanup(cleanupBinding)
})
