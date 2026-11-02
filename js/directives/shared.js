
export function toggleBooleanStateDirective(el, directive, isTruthy) {
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
        let display = (['inline', 'list-item', 'block', 'table', 'flex', 'grid', 'inline-flex']
            .filter(i => directive.modifiers.includes(i))[0] || 'inline-block')

        el.style.display = isTruthy ? display : 'none'
    }
}
