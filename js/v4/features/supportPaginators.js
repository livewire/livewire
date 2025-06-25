import { on } from '@/hooks'

let paginatorObjects = new WeakMap

export function getPaginatorObject(component, paginator = { hasNextPage: false, hasPreviousPage: false }) {
    let paginatorObject = paginatorObjects.get(component)

    if (! paginatorObject) {
        paginatorObject = Alpine.reactive({
            hasNextPage: paginator.hasNextPage,
            hasPreviousPage: paginator.hasPreviousPage,
            nextPage: () => component.$wire.call('nextPage'),
            previousPage: () => component.$wire.call('previousPage'),
        })
    }

    paginatorObjects.set(component, paginatorObject)

    return paginatorObject
}

on('effect', ({ component, effects, cleanup }) => {
    let paginators = effects['paginators']

    if (! paginators) return

    // Only support the "page" paginator for now...
    let paginator = paginators['page']

    if (! paginator) return

    let paginatorObject = getPaginatorObject(component, paginator)

    paginatorObject.hasNextPage = paginator.hasNextPage
    paginatorObject.hasPreviousPage = paginator.hasPreviousPage
})
