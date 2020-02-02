import store from '@/Store'

export default function () {
    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('submit')) return

        // Set a forms "disabled" state on inputs and buttons.
        // Livewire will clean it all up automatically when the form
        // submission returns and the new DOM lacks these additions.
        el.el.addEventListener('submit', ()=> {
            component.walk(elem => {
                const node = elem.el

                if (! el.el.contains(node)) return

                if (node.tagName.toLowerCase() === 'button' && node.type === 'submit') {
                    // Disabled submit button.
                    node.disabled = true
                } else if (node.tagName.toLowerCase() === 'input') {
                    // Set any inputs to "read only".
                    node.readOnly = true
                }
            })
        })
    })
}
