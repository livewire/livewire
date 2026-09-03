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
    let usedPersists = []
    let putBacks = []

    document.querySelectorAll('[x-persist]').forEach(i => {
        let old = els[i.getAttribute('x-persist')]

        // There might be a brand new x-persist element...
        if (! old) return

        usedPersists.push(i.getAttribute('x-persist'))

        old._x_wasPersisted = true

        Alpine.mutateDom(() => {
            i.replaceWith(old)
        })

        putBacks.push([old, i])
    })

    // Wait until every persisted element is back on the page before running the
    // callbacks, otherwise a teleport can't resolve a target inside one of them...
    putBacks.forEach(([old, i]) => callback(old, i))

    Object.entries(els).forEach(([key, el]) => {
        if (usedPersists.includes(key)) return

        // Destory the un-used persist DOM trees before releasing them...
        Alpine.destroyTree(el)
    })

    // Release unused persists for garbage collection...
    els = {}
}

export function isPersistedElement(el) {
    return el.hasAttribute('x-persist')
}
