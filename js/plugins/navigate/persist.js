import Alpine from 'alpinejs'

let els = {}

export function storePersistantElementsForLater(callback) {
    els = {}

    document.querySelectorAll(`[${Alpine.prefixed('persist')}]`).forEach(i => {
        els[i.getAttribute(Alpine.prefixed('persist'))] = i

        callback(i)

        Alpine.mutateDom(() => {
            i.remove()
        })
    })
}

export function putPersistantElementsBack(callback) {
    let usedPersists = []

    document.querySelectorAll(`[${Alpine.prefixed('persist')}]`).forEach(i => {
        let old = els[i.getAttribute(Alpine.prefixed('persist'))]

        // There might be a brand new x-persist element...
        if (! old) return

        usedPersists.push(i.getAttribute(Alpine.prefixed('persist')))

        old._x_wasPersisted = true

        callback(old, i)

        Alpine.mutateDom(() => {
            i.replaceWith(old)
        })
    })

    Object.entries(els).forEach(([key, el]) => {
        if (usedPersists.includes(key)) return

        // Destory the un-used persist DOM trees before releasing them...
        Alpine.destroyTree(el)
    })

    // Release unused persists for garbage collection...
    els = {}
}
