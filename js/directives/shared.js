
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
        let displayModifier = ['inline', 'inline-block', 'list-item', 'block', 'table', 'flex', 'grid', 'inline-flex']
            .find(i => directive.modifiers.includes(i))

        // Without a display modifier, the element's own CSS decides how it's shown.
        // We just lift the stylesheet rule that hides it (see FrontendAssets)...
        let hiddenByStylesheet = ! directive.modifiers.includes('remove')
            && ! (directive.value === 'dirty' && el.matches('input, textarea, select'))

        if (! displayModifier && hiddenByStylesheet) {
            el.toggleAttribute(`data-livewire-${directive.value}-active`, isTruthy)

            return
        }

        let cache = cachedDisplay ?? window
            .getComputedStyle(el, null)
            .getPropertyValue('display')

        let display = displayModifier || 'inline-block'

        // If element is to be removed, set display to its current value...
        display = (directive.modifiers.includes('remove') && ! isTruthy)
            ? cache : display

        el.style.display = isTruthy ? display : 'none'
    }
}
