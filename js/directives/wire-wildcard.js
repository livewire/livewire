import { callAndClearComponentDebounces } from '@/debounce'
import { customDirectiveHasBeenRegistered } from '@/directives'
import { on } from '@/hooks'
import { setNextActionOrigin, setNextActionInterceptor } from '@/request'
import Alpine from 'alpinejs'
import { evaluateActionExpression } from '../evaluator'

on('directive.init', ({ el, directive, cleanup, component }) => {
    if (['snapshot', 'effects', 'model', 'init', 'loading', 'poll', 'ignore', 'id', 'data', 'key', 'target', 'dirty', 'sort'].includes(directive.value)) return
    if (customDirectiveHasBeenRegistered(directive.value)) return

    let attribute = directive.rawName.replace('wire:', 'x-on:')

    // Automatically add .prevent to wire:submit, if they didn't add it themselves...
    if (directive.value === 'submit' && ! directive.modifiers.includes('prevent')) {
        attribute = attribute + '.prevent'
    }

    // Strip .async from Alpine expression because it only concerns Livewire and trips up Alpine...
    if (directive.modifiers.includes('async')) {
        attribute = attribute.replace('.async', '')
    }

    // Strip .renderless from Alpine expression because it only concerns Livewire and trips up Alpine...
    if (directive.modifiers.includes('renderless')) {
        attribute = attribute.replace('.renderless', '')
    }

    // Strip .prepend from Alpine expression because it only concerns Livewire and trips up Alpine...
    if (directive.modifiers.includes('prepend')) {
        attribute = attribute.replace('.prepend', '')
    }

    // Strip .append from Alpine expression because it only concerns Livewire and trips up Alpine...
    if (directive.modifiers.includes('append')) {
        attribute = attribute.replace('.append', '')
    }

    let cleanupBinding = Alpine.bind(el, {
        [attribute](e) {
            directive.eventContext = e
            directive.wire = component.$wire

            let execute = () => {
                callAndClearComponentDebounces(component, () => {
                    // For wire:submit, apply data-loading to the submit button, not the form
                    if (directive.value === 'submit') {
                        let submitButton = e.submitter || el.querySelector('button[type="submit"], input[type="submit"]')
                        setNextActionOrigin({ el, directive, targetEl: submitButton })
                    } else {
                        setNextActionOrigin({ el, directive })
                    }

                    // Check for Livewire event options in $event.detail.livewire
                    let livewireOptions = e.detail?.livewire

                    if (livewireOptions?.interceptAction) {
                        setNextActionInterceptor(livewireOptions.interceptAction)
                    }

                    let expression = directive.expression

                    // Handle defaultParams - if expression has no parentheses, append the default params
                    // This uses a simple heuristic: if the expression contains '(', we assume params
                    // are already provided. This works for common cases like "someMethod" but will
                    // skip adding params for edge cases like "someMethod.bind(this)" or "obj['method()']"
                    if (livewireOptions?.defaultParams !== undefined && !expression.includes('(')) {
                        let params = Array.isArray(livewireOptions.defaultParams)
                            ? livewireOptions.defaultParams
                            : [livewireOptions.defaultParams]
                        expression = expression + '(' + params.map((p) => JSON.stringify(p)).join(", ") + ')'
                    }

                    evaluateActionExpression(el, expression, { scope: { $event: e } })
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
