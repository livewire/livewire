import Alpine from 'alpinejs'

let els = {}

export function storePersistantElementsForLater(callback) {
    els = {}

    document.querySelectorAll('[x-persist]').forEach(i => {
        els[i.getAttribute('x-persist')] = i

        callback(i)

        Alpine.mutateDom(() => {
            i.remove()
        })
    })
}

export function putPersistantElementsBack(callback) {
    document.querySelectorAll('[x-persist]').forEach(i => {
        let old = els[i.getAttribute('x-persist')]

        // There might be a brand new x-persist element...
        if (! old) return

        old._x_wasPersisted = true

        callback(old, i)

        Alpine.mutateDom(() => {
            i.replaceWith(old)
        })
    })
}
