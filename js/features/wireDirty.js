import { toggleBooleanStateDirective } from 'directives/shared'
import { findComponent } from 'state'
import { dataGet, WeakBag } from 'utils'
import { directives as getDirectives } from '../directives'
import { on } from './../synthetic/index'

export default function () {
    let refreshDirtyStatesByComponent = new WeakBag

    on('target.request', (target) => {
        let component = findComponent(target.__livewireId)

        return () => {
            setTimeout(() => { // Doing a "setTimeout" to let morphdom do its thing first...
                refreshDirtyStatesByComponent.each(component, i => i(false))
            })
        }
    })

    on('element.init', (el, component) => {
        let directives = getDirectives(el)

        if (directives.missing('dirty')) return

        let directive = directives.get('dirty')

        let inverted = boolean => directive.modifiers.includes('remove') ? ! boolean : boolean

        let targets = dirtyTargets(directives)

        let dirty = Alpine.reactive({ state: false })

        let oldIsDirty = false

        let refreshDirtyState = (isDirty) => {
            toggleBooleanStateDirective(el, directive, isDirty)

            oldIsDirty = isDirty
        }

        refreshDirtyStatesByComponent.add(component, refreshDirtyState)

        Alpine.effect(() => {
            let isDirty = false

            if (targets.length === 0) {
                isDirty = JSON.stringify(component.synthetic.canonical) !== JSON.stringify(component.synthetic.reactive)
            } else {
                for (let i = 0; i < targets.length; i++) {
                    if (isDirty) break;

                    let target = targets[i]

                    isDirty = JSON.stringify(dataGet(component.synthetic.canonical, target)) !== JSON.stringify(dataGet(component.synthetic.reactive, target))
                }
            }

            if (oldIsDirty !== isDirty) {
                refreshDirtyState(isDirty)
            }

            oldIsDirty = isDirty
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

function setToggleDirective(el, directive, isTruthy) {
    let directive = getDirectives(el).get('dirty')

    if (directive.modifiers.includes('class')) {
        let classes = directive.value.split(' ')

        if (isDirty) {
            el.classList.add(...classes)
        } else {
            el.classList.remove(...classes)
        }
    } else if (directive.modifiers.includes('attr')) {
        if (isDirty) {
            el.setAttribute(directive.value, true)
        } else {
            el.removeAttribute(directive.value)
        }
    } else if (! getDirectives(el).get('model')) {
        el.style.display = isDirty ? 'inline-block' : 'none'
    }
}
