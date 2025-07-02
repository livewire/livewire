import { callAndClearComponentDebounces } from '@/debounce'
import { customDirectiveHasBeenRegistered } from '@/directives'
import { on } from '@/hooks'
import { implicitIslandHook, wireIslandHook } from '@/v4/features/supportWireIsland'
import Alpine from 'alpinejs'
import interceptor from '@/v4/interceptors/interceptors.js'

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
                    // @todo: this is a V4 hack to get data-loading working...
                    let evaluator = Alpine.evaluateLater(
                        el,
                        'await $wire.'+directive.expression,
                        { scope: { $event: e }},
                    )

                    interceptor.fire(el, directive, component)

                    el.setAttribute('data-loading', 'true')

                    // @todo: this is a V4 hack to get wire:island working...
                    // wireIslandHook(el)

                    // implicitIslandHook(el)

                    evaluator(() => {
                        el.removeAttribute('data-loading')
                    });
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
