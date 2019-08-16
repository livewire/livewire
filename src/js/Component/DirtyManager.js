import DOMElement from '@/dom/dom_element'

class DirtyManager {
    constructor(component) {
        this.component = component
        this.dirtyElsByRef = {}
        this.dirtyEls = []
    }

    addDirtyEls(el, targetRefs) {
        if (targetRefs) {
            targetRefs.forEach(targetRef => {
                if (this.dirtyElsByRef[targetRef]) {
                    this.dirtyElsByRef[targetRef].push(el)
                } else {
                    this.dirtyElsByRef[targetRef] = [el]
                }
            })
        } else {
            this.dirtyEls.push(el)
        }
    }

    registerListener() {
        this.component.el.addEventListener('input', (e) => {
            const el = new DOMElement(e.target)

            let allEls = []

            if (el.directives.has('ref') && this.dirtyElsByRef[el.directives.get('ref').value]) {
                allEls.push(...this.dirtyElsByRef[el.directives.get('ref').value])
            }

            if (el.directives.has('dirty')) {
                allEls.push(...this.dirtyEls.filter(dirtyEl => dirtyEl.directives.get('model').value === el.directives.get('model').value))
            }

            if (allEls.length < 1) return

            if (el.directives.missing('model')) {
                console.warn('`wire:model` must be present on any element that uses `wire:dirty` or is a `wire:dirty` target.')
            }

            const isDirty = el.valueFromInput() != this.component.data[el.directives.get('model').value]

            allEls.forEach(el => {
                this.setDirtyState(el, isDirty)
            });
        })
    }

    setDirtyState(el, isDirty) {
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
}

export default DirtyManager
