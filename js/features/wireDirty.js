import { directives } from '../directives'
import { on } from '../events'

export default function () {
    on('component.initialized', (component) => {
        component.dirty = Alpine.reactive({ state: false })
    })

    on('element.init', (el, component) => {
        let allDirectives = directives(el)

        if (allDirectives.missing('dirty')) return
        let directive = allDirectives.get('dirty')

        let inverted = boolean => directive.modifiers.includes('remove') ? ! boolean : boolean

        let targets = dirtyTargets(allDirectives)

        let dirty = Alpine.reactive({ state: false })

        Alpine.effect(() => {
            let isDirty = false

            if (targets.length === 0) {
                isDirty = JSON.stringify(component.canonicalData) !== JSON.stringify(component.dataReactive)
            } else {
                for (let i = 0; i < targets.length; i++) {
                    if (isDirty) break;

                    let target = targets[i]

                    isDirty = JSON.stringify(component.canonicalData[target]) !== JSON.stringify(component.dataReactive[target])
                }
            }

            if (dirty.state !== isDirty) dirty.state = isDirty
        })

        Alpine.effect(() => {
            console.log(dirty.state)
            setDirtyState(el, inverted(dirty.state))
        })
    })
}

function dirtyTargets(directives) {
    let targets = []

    if (directives.has('model')) {
        targets.push(directives.get('model').value)
    }

    if (directives.has('target')) {
        targets = targets.concat(
            directives
            .get('target')
            .value.split(',')
            .map(s => s.trim())
        )
    }

    return targets
}

function setDirtyState(el, isDirty) {
    let directive = directives(el).get('dirty')

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
    } else if (! directives(el).get('model')) {
        el.style.display = isDirty ? 'inline-block' : 'none'
        el.__livewire_dirty_cleanup = () =>
            (el.style.display = isDirty ? 'none' : 'inline-block')
    }
}
