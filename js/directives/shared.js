
export function toggleBooleanStateDirective(el, directive, isTruthy, cachedDisplay = null) {
    isTruthy = directive.hasModifier('remove') ? ! isTruthy : isTruthy

    if (directive.hasModifier('class')) {
        let classes = directive.expression.split(' ').filter(String)

        if (isTruthy) {
            el.classList.add(...classes)
        } else {
            el.classList.remove(...classes)
        }
    } else if (directive.hasModifier('attr')) {
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
            .filter(i => directive.hasModifier(i))[0] || 'inline-block')

        // If element is to be removed, set display to its current value...
        // display = (directive.hasModifier('remove') && ! isTruthy)
        display = (directive.hasModifier('remove') && ! isTruthy)
            ? cache : display

        el.style.display = isTruthy ? display : 'none'
    }
}
