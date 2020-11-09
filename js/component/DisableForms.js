import store from '@/Store'
import { wireDirectives } from '../util'

let cleanupStackByComponentId = {}

export default function () {
    store.registerHook('element.initialized', (el, component) => {
        let directives = wireDirectives(el)

        if (directives.missing('submit')) return

        // Set a forms "disabled" state on inputs and buttons.
        // Livewire will clean it all up automatically when the form
        // submission returns and the new DOM lacks these additions.
        el.addEventListener('submit', () => {
            cleanupStackByComponentId[component.id] = []

            component.walk(node => {
                if (! el.contains(node)) return

                if (node.hasAttribute('wire:ignore')) return false

                if (
                    // <button type="submit">
                    (node.tagName.toLowerCase() === 'button' &&
                        node.type === 'submit') ||
                    // <select>
                    node.tagName.toLowerCase() === 'select' ||
                    // <input type="checkbox|radio">
                    (node.tagName.toLowerCase() === 'input' &&
                        (node.type === 'checkbox' || node.type === 'radio'))
                ) {
                    if (!node.disabled)
                        cleanupStackByComponentId[component.id].push(
                            () => (node.disabled = false)
                        )

                    node.disabled = true
                } else if (
                    // <input type="text">
                    node.tagName.toLowerCase() === 'input' ||
                    // <textarea>
                    node.tagName.toLowerCase() === 'textarea'
                ) {
                    if (!node.readOnly)
                        cleanupStackByComponentId[component.id].push(
                            () => (node.readOnly = false)
                        )

                    node.readOnly = true
                }
            })
        })
    })

    store.registerHook('message.failed', (message, component) => cleanup(component))
    store.registerHook('message.received', (message, component) => cleanup(component))
}

function cleanup(component) {
    if (!cleanupStackByComponentId[component.id]) return

    while (cleanupStackByComponentId[component.id].length > 0) {
        cleanupStackByComponentId[component.id].shift()()
    }
}
