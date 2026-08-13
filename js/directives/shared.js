
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
            .filter(i => directive.modifiers.includes(i))[0] || 'inline-block')

        // If element is to be removed, set display to its current value...
        // display = (directive.modifiers.includes('remove') && ! isTruthy)
        display = (directive.modifiers.includes('remove') && ! isTruthy)
            ? cache : display

        el.style.display = isTruthy ? display : 'none'
    }
}

function parseComponentEffect(component, expression, key)
{
    let target = component
    let name = expression

    // 1) wire:model.live="$parent.foo" commits on the parent — look up parent effects
    if (name.startsWith('$parent')) {
        target = target.parent

        if (! target) return undefined

        return parseComponentEffect(target, name.replace(/^\$parent\.?/, ''), key)
    }

    let parenthesesIndex = name.indexOf('(')
    if (parenthesesIndex !== -1) {
        name = name.slice(0, parenthesesIndex).trim()
    }

    // 2) Mount-time effect — prefer originalEffects because
    //    mergeNewSnapshot replaces component.effects on subsequent requests
    //    (same durability pattern as originalEffects.url / scripts)
    let options = (target.originalEffects && target.originalEffects[key])
        || target.effects[key]
        || {}

    return options[name]
}

export function debounceEffectDuration(component, expression)
{
    let duration = parseComponentEffect(component, expression, 'debounce')

    return ! isNaN(duration) ? duration : undefined
}

export function hasDebounceEffect(component, expression)
{
    return debounceEffectDuration(component, expression) !== undefined
}
