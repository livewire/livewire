
export function debounceEffectDuration(component, expression)
{
    let duration = parseComponentEffect(component, expression, 'debounce')

    return ! isNaN(duration) ? duration : undefined
}

export function hasDebounceEffect(component, expression)
{
    return debounceEffectDuration(component, expression) !== undefined
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
