
export function toggleBooleanStateDirective(el, directive, isTruthy, cachedDisplay = null) {
    isTruthy = directive.modifiers.includes('remove') ? ! isTruthy : isTruthy

    if (directive.modifiers.includes('class')) {
        let classes = directive.expression.split(' ').filter(String)

        if (isTruthy) {
            el.classList.add(...classes)
        } else {
            el.classList.remove(...classes)
        }
    } else if (directive.modifiers.includes('attr')) {
        if (isTruthy) {
            el.setAttribute(directive.expression, true)
        } else {
            el.removeAttribute(directive.expression)
        }
    } else {
        let cache = cachedDisplay ?? window
            .getComputedStyle(el, null)
            .getPropertyValue('display')

        let display = (['inline', 'list-item', 'block', 'table', 'flex', 'grid', 'inline-flex']
            .filter(i => directive.modifiers.includes(i))[0] || ownDisplay(el, cache))

        // If element is to be removed, set display to its current value...
        // display = (directive.modifiers.includes('remove') && ! isTruthy)
        display = (directive.modifiers.includes('remove') && ! isTruthy)
            ? cache : display

        el.style.display = isTruthy ? display : 'none'
    }
}

function ownDisplay(el, computedDisplay) {
    // The element is currently visible, so its own display is known...
    if (computedDisplay && computedDisplay !== 'none') return computedDisplay

    // The display was already resolved for this element...
    if (el._livewireOwnDisplay) return el._livewireOwnDisplay

    let measured = measureOwnDisplay(el)

    // The element (or an ancestor) is hidden, so there is nothing meaningful
    // to measure. Fall back to the historical default...
    if (! measured || measured === 'none') return 'inline-block'

    el._livewireOwnDisplay = measured

    return measured
}

function measureOwnDisplay(el) {
    if (! el.parentNode) return 'none'

    // Clone the element without children so no extra resources load, strip the
    // attributes that hide the original, and measure the clone off-screen
    // so the element's own display (classes, inline styles, etc.) applies...
    let clone = el.cloneNode(false)

    Array.from(clone.attributes)
        .map(attribute => attribute.name)
        .filter(name => name.startsWith('wire:') || name === 'hidden')
        .forEach(name => clone.removeAttribute(name))

    clone.style.display = ''
    clone.style.position = 'absolute'
    clone.style.visibility = 'hidden'

    el.parentNode.appendChild(clone)

    let display = window.getComputedStyle(clone).display

    clone.remove()

    return display
}
