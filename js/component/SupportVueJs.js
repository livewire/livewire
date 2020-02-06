import store from '@/Store'
import ModelAction from '@/action/model'

export default function () {
    store.registerHook('beforeDomUpdate', (component, mutableHtmlContainer) => {
        if (! window.Vue) return

        const div = document.createElement('div')
        div.innerHTML =  mutableHtmlContainer.html

        new window.Vue().$mount(div.firstElementChild)

        mutableHtmlContainer.html = div.firstElementChild.outerHTML
    })

    store.registerHook('elementInitialized', el => {
        if (el.rawNode().__vue__) el.rawNode().__livewire_ignore = true
    })

    store.registerHook('interceptWireModelSetValue', (el, value) => {
        // If it's a vue component pass down the value prop.
        if (! el.rawNode().__vue__) return

        // Also, Vue will throw a warning because we are programmaticallly
        // setting a prop, we need to silence that.
        const originalSilent = window.Vue.config.silent
        window.Vue.config.silent = true

        el.rawNode().__vue__.$props.value = value

        window.Vue.config.silent = originalSilent
    })

    store.registerHook('interceptWireModelAttachListener', (el, directive, component, debounceIf) => {
        // If it's a vue component pass down the value prop.
        if (! el.rawNode().__vue__) return

        const hasDebounceModifier = directive.modifiers.includes('debounce')
        const isLazy = directive.modifiers.includes('lazy')

        el.rawNode().__vue__.$on('input', debounceIf(hasDebounceModifier || ! isLazy, e => {
            const model = directive.value
            const value = e

            component.addAction(new ModelAction(model, value, el))
        }, directive.durationOr(150)))
    })
}
