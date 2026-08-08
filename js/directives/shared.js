
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
        let attribute = directive.expression
        let hadAttribute = el.hasAttribute(attribute)
        let value = el.getAttribute(attribute)

        // A callback that puts the attribute back exactly as it was before loading...
        let restore = hadAttribute
            ? () => el.setAttribute(attribute, value)
            : () => el.removeAttribute(attribute)

        if (isTruthy) {
            el.setAttribute(attribute, true)
        } else {
            el.removeAttribute(attribute)
        }

        return restore
    } else {
        let cache = cachedDisplay ?? window
            .getComputedStyle(el, null)
            .getPropertyValue('display')

        let display = (['inline', 'list-item', 'block', 'table', 'flex', 'grid', 'inline-flex']
            .filter(i => directive.modifiers.includes(i))[0] || 'inline-block')

        // If element is to be removed, set display to its current value...
        // display = (directive.modifiers.includes('remove') && ! isTruthy)
        display = (directive.modifiers.includes('remove') && ! isTruthy)
            ? cache : display

        el.style.display = isTruthy ? display : 'none'
    }
}
