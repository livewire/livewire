import { getDirectives } from '@/directives'
import { on } from '@/events'
import { Livewire } from '@/index'
import Alpine from 'alpinejs'

let cleanupStackByComponentId = {}

on('element.init', ({ el, component }) => {
    let directives = getDirectives(el)

    if (directives.missing('submit')) return

    // Set a forms "disabled" state on inputs and form element.
    // Livewire will clean it all up automatically when the form
    // submission returns and the new DOM lacks these additions.
    el.addEventListener('submit', (event) => {

        // If the form is already submitting, do nothing
        // if (el.hasAttribute('data-submitting')) {
        //     // event.stopPropagation();
        //     return;
        // }

        cleanupStackByComponentId[component.id] = []

        // el.setAttribute('data-submitting', true);

        cleanupStackByComponentId[component.id].push(
            () => (el.removeAttribute('data-submitting'))
        )


        Alpine.walk(component.el, (node, skip) => {
            if (! el.contains(node)) return

            if (node.hasAttribute('wire:ignore')) return skip()

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
                return;
            }

            if (
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

on('commit', ({ component, respond }) => {
    respond(() => {
        cleanup(component)
    })
})

function cleanup(component) {
    if (!cleanupStackByComponentId[component.id]) return

    while (cleanupStackByComponentId[component.id].length > 0) {
        cleanupStackByComponentId[component.id].shift()()
    }
}
