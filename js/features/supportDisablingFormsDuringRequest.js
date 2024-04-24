import { getDirectives } from '@/directives'
import { on } from '@/hooks'
import { Bag } from '@/utils'
import Alpine from 'alpinejs'

let cleanups = new Bag

// Adding a setTimeout here so that these event listeners are
// registered AFTER most other event listenrs, this way, we
// can call "stopPropagation" for things like wire:confirm
on('directive.init', ({ el, directive, cleanup, component }) => setTimeout(() => {
    if (directive.value !== 'submit') return

    // Livewire will clean it all up automatically when the form
    // submission returns and the new DOM lacks these additions...
    el.addEventListener('submit', () => {
        // If using wire:submit="$parent...", we will need to use
        // the parent ID as a reference for undoing because it's
        // the ID that will come back from the network request.
        let componentId = directive.expression.startsWith('$parent')
            ? component.parent.id
            : component.id

        let cleanup = disableForm(el)

        cleanups.add(componentId, cleanup)
    })
}))

on('commit', ({ component, respond }) => {
    respond(() => {
        cleanups.each(component.id, i => i())
        cleanups.remove(component.id)
    })
})

function disableForm(formEl) {
    let undos = []

    Alpine.walk(formEl, (el, skip) => {
        if (! formEl.contains(el)) return

        if (el.hasAttribute('wire:ignore')) return skip()

        if (shouldMarkDisabled(el)) {
            undos.push(markDisabled(el))
        } else if (shouldMarkReadOnly(el)) {
            undos.push(markReadOnly(el))
        }
    })

    return () => {
        while (undos.length > 0) undos.shift()()
    }
}

function shouldMarkDisabled(el) {
    let tag = el.tagName.toLowerCase()

    if (tag === 'select') return true
    if (tag === 'button' && el.type === 'submit') return true
    if (tag === 'input' && (el.type === 'checkbox' || el.type === 'radio')) return true

    return false
}

function shouldMarkReadOnly(el) {
    return ['input', 'textarea'].includes(el.tagName.toLowerCase())
}

function markDisabled(el) {
    let undo = el.disabled
        ? () => {}
        : () => el.disabled = false

    el.disabled = true

    return undo
}

function markReadOnly(el) {
    let undo = el.readOnly
        ? () => {}
        : () => el.readOnly = false

    el.readOnly = true

    return undo
}
