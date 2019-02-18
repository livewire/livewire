const prefix = require('./prefix.js')()

export function getAttribute(el, attribute) {
    return el.getAttribute(`${prefix}:${attribute}`)
}

export function hasAttribute(el, attribute) {
    return el.hasAttribute(`${prefix}:${attribute}`)
}

export function closestByAttribute(el, attribute) {
    return el.closest(`[${prefix}\\:${attribute}]`)
}

export function elByAttributeAndValue(attribute, value) {
    return document.querySelector(`[${prefix}\\:${attribute}="${value}"]`)
}

export function preserveActiveElement(callback) {
    const activeElement = document.activeElement

    callback()

    activeElement.focus()
}

export function elsByAttributeAndValue(attribute, value, scope) {
    return (scope || document).querySelectorAll(`[${prefix}\\:${attribute}="${value}"]`)
}

export function elByAttribute(attribute) {
    return document.querySelector(`[${prefix}\\:${attribute}]`)
}

export function elsByAttribute(attribute) {
    return document.querySelectorAll(`[${prefix}\\:${attribute}]`)
}

export function extractDirectivesModifiersAndValuesFromEl(el) {
    let directives = {}

    el.getAttributeNames()
        // Filter only the livewire directives.
        .filter(name => name.match(new RegExp(prefix + ':')))
        // Parse out the event, modifiers, and value from it.
        .forEach(name => {
            let directive, modifiers
            [directive, ...modifiers] = name.replace(new RegExp(prefix + ':'), '').split('.')

            directives[directive] = { modifiers, value: el.getAttribute(name) }
        })

    return directives
}
