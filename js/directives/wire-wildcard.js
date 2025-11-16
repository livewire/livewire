import { callAndClearComponentDebounces } from '@/debounce'
import { customDirectiveHasBeenRegistered } from '@/directives'
import { on } from '@/hooks'
import { setNextActionOrigin } from '@/request'
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

                    evaluateActionExpression(component, el, directive.expression, { scope: { $event: e } })
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
