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

                if (
                    // <button type="submit">
                    (node.tagName.toLowerCase() === 'button' && node.type === 'submit')
                    // <select>
                    || (node.tagName.toLowerCase() === 'select')
                    // <input type="checkbox|radio">
                    || (node.tagName.toLowerCase() === 'input' && (node.type === 'checkbox' || node.type === 'radio'))
                ) {
                    node.disabled = true
                } else if (
                    // <input type="text">
                    node.tagName.toLowerCase() === 'input'
                    // <textarea>
                    || node.tagName.toLowerCase() === 'textarea'
                ) {
                    node.readOnly = true
                }
            })
        })
    })
}
