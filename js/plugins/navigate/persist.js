import Alpine from 'alpinejs'

let els = {}

export function storePersistentElementsForLater(callback) {
    els = {}

    document.querySelectorAll('[x-persist]').forEach(i => {
        els[i.getAttribute('x-persist')] = i

        callback(i)

        Alpine.mutateDom(() => {
            i.remove()
        })
    })
}

export function putPersistentElementsBack(callback) {
    let usedPersists = []

    document.querySelectorAll('[x-persist]').forEach(i => {
        let old = els[i.getAttribute('x-persist')]

        // There might be a brand new x-persist element...
        if (! old) return

        usedPersists.push(i.getAttribute('x-persist'))

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
