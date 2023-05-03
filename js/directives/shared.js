
export function toggleBooleanStateDirective(el, directive, isTruthy) {
    isTruthy = directive.modifiers.includes('remove') ? ! isTruthy : isTruthy

    if (directive.modifiers.includes('class')) {
        let classes = directive.expression.split(' ')

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
        let cache = window
            .getComputedStyle(el, null)
            .getPropertyValue('display')
            
        let display = (['inline', 'block', 'table', 'flex', 'grid', 'inline-flex']
            .filter(i => directive.modifiers.includes(i))[0] || 'inline-block')

        // If element is to be removed, set display to it's current value
        display = directive.modifiers.includes('remove') ? cache : display

        el.style.display = isTruthy ? display : 'none'
    }
}
