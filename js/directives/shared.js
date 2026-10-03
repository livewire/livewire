
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
        let displayModifier = [
            'inline',
            'list-item',
            'block',
            'table',
            'flex',
            'grid',
            'inline-flex',
            // "inline-block" was never listed here because it used to fall through
            // to the "inline-block" default. Now that the default is only used for
            // explicitly opted-in display values, it needs to be detected too...
            'inline-block',
        ].find(i => directive.modifiers.includes(i))

        let canPreserveDisplay = directive.value !== 'dirty' || ! el.matches('input, textarea, select')

        if (! displayModifier && ! directive.modifiers.includes('remove') && canPreserveDisplay) {
            let activeAttribute = `data-livewire-${directive.value}-active`

            if (isTruthy) {
                el.setAttribute(activeAttribute, '')
            } else {
                el.removeAttribute(activeAttribute)
            }

            return
        }

        let cache =
            cachedDisplay ??
            window.getComputedStyle(el, null).getPropertyValue('display')

        let display = displayModifier || 'inline-block'

        display =
            directive.modifiers.includes('remove') && ! isTruthy
                ? cache
                : display

        el.style.display = isTruthy ? display : 'none'
    }
}
