import store from '@/Store'

export default function () {
    store.registerHook('componentInitialized', component => {
        component.dirtyEls = []
    })

    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('dirty')) return

        component.dirtyEls.push(el)
    })

    store.registerHook(
        'interceptWireModelAttachListener',
        (el, directive, component) => {
            let property = directive.value

            el.el.addEventListener('input', () => {
                component.dirtyEls.forEach(dirtyEl => {
                    if (
                        (dirtyEl.directives.has('model') &&
                            dirtyEl.directives.get('model').value ===
                                property) ||
                        (dirtyEl.directives.has('target') &&
                            dirtyEl.directives
                                .get('target')
                                .value.split(',')
                                .map(s => s.trim())
                                .includes(property))
                    ) {
                        let isDirty =
                            el.valueFromInput(component) !=
                            component.get(property)

                        setDirtyState(dirtyEl, isDirty)
                    }
                })
            })
        }
    )

    store.registerHook('responseReceived', component => {
        component.dirtyEls.forEach(element => {
            if (element.__livewire_dirty_cleanup) {
                element.__livewire_dirty_cleanup()
                delete element.__livewire_dirty_cleanup
            }
        })
    })

    store.registerHook('elementRemoved', (el, component) => {
        component.dirtyEls.forEach((element, index) => {
            if (element.isSameNode(el)) {
                component.dirtyEls.splice(index, 1)
            }
        })
    })
}

function setDirtyState(el, isDirty) {
    const directive = el.directives.get('dirty')

    if (directive.modifiers.includes('class')) {
        const classes = directive.value.split(' ')
        if (directive.modifiers.includes('remove') !== isDirty) {
            el.classList.add(...classes)
            el.__livewire_dirty_cleanup = () => el.classList.remove(...classes)
        } else {
            el.classList.remove(...classes)
            el.__livewire_dirty_cleanup = () => el.classList.add(...classes)
        }
    } else if (directive.modifiers.includes('attr')) {
        if (directive.modifiers.includes('remove') !== isDirty) {
            el.setAttribute(directive.value, true)
            el.__livewire_dirty_cleanup = () =>
                el.removeAttribute(directive.value)
        } else {
            el.removeAttribute(directive.value)
            el.__livewire_dirty_cleanup = () =>
                el.setAttribute(directive.value, true)
        }
    } else if (!el.directives.get('model')) {
        el.el.style.display = isDirty ? 'inline-block' : 'none'
        el.__livewire_dirty_cleanup = () =>
            (el.el.style.display = isDirty ? 'none' : 'inline-block')
    }
}
