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

export function isComponentRootEl(el) {
    return hasAttribute(el, 'root-id')
}

export function transitionElementIn(el) {
    if (el.hasAttribute(`${prefix}:transition`)) {
        const transitionName = el.getAttribute(`${prefix}:transition`)

        el.classList.add(`${transitionName}-enter`)
        el.classList.add(`${transitionName}-enter-active`)

        setTimeout(() => {
            el.classList.remove(`${transitionName}-enter`)
            setTimeout(() => {
                el.classList.remove(`${transitionName}-enter-active`)
            }, 500)
        }, 65)
    }
}

export function transitionElementOut(el) {
    if (el.hasAttribute(`${prefix}:transition`)) {
        const transitionName = el.getAttribute(`${prefix}:transition`)

        el.classList.add(`${transitionName}-leave-active`)

        setTimeout(() => {
        el.classList.add(`${transitionName}-leave-to`)
            setTimeout(() => {
                el.classList.remove(`${transitionName}-leave-active`)
                el.classList.remove(`${transitionName}-leave-to`)
                el.remove()
            }, 500)
        }, 65)

        return false
    }
    return true
}

export function shouldUpdateInputElementGivenItHasBeenUpdatedViaSync(el, dirtyInputs) {
    // This will need work. But is essentially "input persistance"
    const isInput = (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA')

    if (isInput) {
        if (el.type === 'submit') {
            return true
        }

        const isSync = hasAttribute(el, 'sync')

        if (isSync) {
            const syncName = getAttribute(el, 'sync')
            if (Array.from(dirtyInputs).includes(syncName)) {
                return true
            } {
                return false
            }
        }

        return false
    }
}
