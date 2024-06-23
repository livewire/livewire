import Alpine from "alpinejs"

export function packUpPersistedTeleports(persistedEl) {
    // Persisted elements get removed from the DOM and then re-added later. We need to do the same
    // with any `x-teleport`ed elements...
    Alpine.mutateDom(() => {
        persistedEl.querySelectorAll('[data-teleport-template]').forEach(i => i._x_teleport.remove())
    })
}

export function removeAnyLeftOverStaleTeleportTargets(body) {
    // We need to remove any left-over teleported elements form the page
    // as they are stale and will be re-initialized when Alpine boots up on this page...
    Alpine.mutateDom(() => {
        body.querySelectorAll('[data-teleport-target]').forEach(i => i.remove())
    })
}

export function unPackPersistedTeleports(persistedEl) {
    // Before we put back any persisted elements, we're going to
    // find any "x-teleports" and put their targets back on the page...
    Alpine.walk(persistedEl, (el, skip) => {
        if (! el._x_teleport) return;

        el._x_teleportPutBack()

        skip()
    })
}

export function isTeleportTarget(el) {
    return el.hasAttribute('data-teleport-target')
}
