
export function findRefEl(component, name) {
    let refEl = component.el.querySelector(`[wire\\:ref="${name}"]`)

    if (! refEl) return console.error(`Ref "${name}" not found in component "${component.id}"`)

    return refEl
}
