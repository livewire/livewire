import Alpine from 'alpinejs'

let els = {}

export function storePersistantElementsForLater() {
    els = {}

    document.querySelectorAll(`[${Alpine.prefixed('persist')}]`).forEach(i => {
        els[i.getAttribute(Alpine.prefixed('persist'))] = i

        Alpine.mutateDom(() => {
            i.remove()
        })
    })
}

export function putPersistantElementsBack() {
    document.querySelectorAll(`[${Alpine.prefixed('persist')}]`).forEach(i => {
        let old = els[i.getAttribute(Alpine.prefixed('persist'))]

        // There might be a brand new x-persist element...
        if (! old) return

        old._x_wasPersisted = true

        Alpine.mutateDom(() => {
            i.replaceWith(old)
        })
    })
}
