import DOMElement from '@/dom/dom_element'
import store from '@/Store'

export default function () {
    store.registerHook('componentInitialized', component => {
        component.targetedDirtyElsByProperty = {}
        component.genericDirtyEls = []

        registerListener(component)
    })

    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('dirty')) return

        const propertyNames = el.directives.has('target')
            && el.directives.get('target').value.split(',').map(s => s.trim())

        addDirtyEls(
            component,
            el,
            propertyNames,
        )
    })

    store.registerHook('elementRemoved', (el, component) => {
        // Look through the targeted elements to remove.
        Object.keys(component.targetedDirtyElsByProperty).forEach(key => {
            component.targetedDirtyElsByProperty[key] = component.targetedDirtyElsByProperty[key].filter(element => {
                return ! element.isSameNode(el)
            })
        })

        // Look through the global/generic elements for the element to remove.
        component.genericDirtyEls.forEach((element, index) => {
            if (element.isSameNode(el)) {
                component.genericDirtyEls.splice(index, 1)
            }
        })
    })
}

function addDirtyEls(component, el, targetProperties) {
        if (targetProperties) {
            targetProperties.forEach(targetProperty => {
                if (component.targetedDirtyElsByProperty[targetProperty]) {
                    component.targetedDirtyElsByProperty[targetProperty].push(el)
                } else {
                    component.targetedDirtyElsByProperty[targetProperty] = [el]
                }
            })
        } else {
            component.genericDirtyEls.push(el)
        }
    }

function registerListener(component) {
    component.el.addEventListener('input', (e) => {
        const el = new DOMElement(e.target)

        let allEls = []

        if (el.directives.has('model') && component.targetedDirtyElsByProperty[el.directives.get('model').value]) {
            allEls.push(...component.targetedDirtyElsByProperty[el.directives.get('model').value])
        }

        if (el.directives.has('dirty')) {
            allEls.push(...component.genericDirtyEls.filter(dirtyEl => dirtyEl.directives.get('model').value === el.directives.get('model').value))
        }

        if (allEls.length < 1) return

        if (el.directives.missing('model')) {
            console.warn('`wire:model` must be present on any element that uses `wire:dirty` or is a `wire:dirty` target.')
        }

        const isDirty = el.valueFromInput(component) != component.data[el.directives.get('model').value]

        allEls.forEach(el => {
            setDirtyState(el, isDirty)
        });
    })
}

function setDirtyState(el, isDirty) {
    const directive = el.directives.get('dirty')

    if (directive.modifiers.includes('class')) {
        const classes = directive.value.split(' ')
        if (directive.modifiers.includes('remove') !== isDirty) {
            el.classList.add(...classes)
        } else {
            el.classList.remove(...classes)
        }
    } else if (directive.modifiers.includes('attr')) {
        if (directive.modifiers.includes('remove') !== isDirty) {
            el.setAttribute(directive.value, true)
        } else {
            el.removeAttrsibute(directive.value)
        }
    } else if (! el.directives.get('model')) {
        el.el.style.display = isDirty ? 'inline-block' : 'none'
    }
}
