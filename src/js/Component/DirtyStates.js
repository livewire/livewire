import DOMElement from '@/dom/dom_element'
import store from '@/Store'

export default function () {
    store.registerHook('componentInitialized', component => {
        component.dirtyElsByRef = {}
        component.dirtyEls = []

        registerListener(component)
    })

    store.registerHook('elementInitialized', (el, component) => {
        if (el.directives.missing('dirty')) return

        const refNames = el.directives.has('target')
            && el.directives.get('target').value.split(',').map(s => s.trim())

        addDirtyEls(
            component,
            el,
            refNames,
        )
    })
}

function addDirtyEls(component, el, targetRefs) {
        if (targetRefs) {
            targetRefs.forEach(targetRef => {
                if (component.dirtyElsByRef[targetRef]) {
                    component.dirtyElsByRef[targetRef].push(el)
                } else {
                    component.dirtyElsByRef[targetRef] = [el]
                }
            })
        } else {
            component.dirtyEls.push(el)
        }
    }

function registerListener(component) {
    component.el.addEventListener('input', (e) => {
        const el = new DOMElement(e.target)

        let allEls = []

        if (el.directives.has('ref') && component.dirtyElsByRef[el.directives.get('ref').value]) {
            allEls.push(...component.dirtyElsByRef[el.directives.get('ref').value])
        }

        if (el.directives.has('dirty')) {
            allEls.push(...component.dirtyEls.filter(dirtyEl => dirtyEl.directives.get('model').value === el.directives.get('model').value))
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
