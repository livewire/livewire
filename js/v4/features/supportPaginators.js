import { on } from '@/hooks'

let paginatorObjects = new WeakMap

export function getPaginatorObject(component) {
    let paginatorObject = paginatorObjects.get(component)

    if (! paginatorObject) {
        paginatorObject = Alpine.reactive({
            renderedPages: [],
            hasEarlier: false,
            hasMore: false,
            get hasNextPage() {
                return paginatorObject.hasMore
            },
            get hasPreviousPage() {
                return paginatorObject.hasEarlier
            },
            previousPage() {
                paginatorObject.loadEarlier()
            },
            nextPage() {
                paginatorObject.loadMore()
            },
            loadEarlier() {
                let sortedPages = paginatorObject.renderedPages.sort((a, b) => a - b)
                let leadingPage = sortedPages[0]

                component.$wire.call('setPage', leadingPage - 1)
            },
            loadMore() {
                let sortedPages = paginatorObject.renderedPages.sort((a, b) => a - b)
                let trailingPage = sortedPages[sortedPages.length - 1]

                component.$wire.call('setPage', trailingPage + 1)
            }
        })

        paginatorObjects.set(component, paginatorObject)
    }

    return paginatorObject
}

on('effect', ({ component, effects, cleanup }) => {
    let paginators = effects['paginators']

    if (! paginators) return

    // Only support the "page" paginator for now...
    let paginator = paginators['page']

    if (! paginator) return

    let paginatorObject = getPaginatorObject(component)

    applyPaginatorToReactiveObject(paginatorObject, paginator)
})

export function applyPaginatorToReactiveObject(paginatorObject, paginator) {
    let currentPage = paginator.currentPage

    paginatorObject.renderedPages.push(paginator.currentPage)

    let sortedPages = paginatorObject.renderedPages.sort((a, b) => a - b)

    if (sortedPages[sortedPages.length - 1] === currentPage) {
        paginatorObject.hasMore = paginator.hasNextPage
    }

    if (sortedPages[0] === currentPage) {
        paginatorObject.hasEarlier = paginator.hasPreviousPage
    }
}