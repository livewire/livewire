import { on } from '@/hooks'

let paginatorObjects = new WeakMap

export function getPaginatorObject(component, paginatorName) {
    let componentPaginatorObjects = paginatorObjects.get(component)

    if (!componentPaginatorObjects) {
        componentPaginatorObjects = new Map

        paginatorObjects.set(component, componentPaginatorObjects)
    }

    let paginatorObject = componentPaginatorObjects.get(paginatorName)

    if (!paginatorObject) {
        paginatorObject = newPaginatorObject(component)

        componentPaginatorObjects.set(paginatorName, paginatorObject)
    }

    return paginatorObject
}

on('effect', ({ component, effects, cleanup }) => {
    let paginators = effects['paginators']

    if (! paginators) return

    for (let paginatorName in paginators) {
        let paginator = paginators[paginatorName]
        let paginatorObject = getPaginatorObject(component, paginatorName)
        paginatorObject.paginator = paginator
    }
})

function newPaginatorObject(component) {
    return Alpine.reactive({
        renderedPages: [],
        paginator: {},
        firstItem() {
            return this.paginator.from
        },
        lastItem() {
            return this.paginator.to
        },
        perPage() {
            return this.paginator.perPage
        },
        onFirstPage() {
            return this.paginator.onFirstPage
        },
        onLastPage() {
            return this.paginator.onLastPage
        },
        getPageName() {
            return this.paginator.pageName
        },
        getCursorName() {
            return this.paginator.cursorName
        },
        currentPage() {
            return this.paginator.currentPage
        },
        currentCursor() {
            return this.paginator.currentCursor
        },
        count() {
            return this.paginator.count
        },
        total() {
            return this.paginator.total
        },
        hasPages() {
            return this.paginator.hasPages
        },
        hasMorePages() {
            return this.paginator.hasMorePages
        },
        hasPreviousPage() {
            return this.hasPages() && ! this.onFirstPage()
        },
        hasNextPage() {
            return this.hasPages() && ! this.onLastPage()
        },
        hasPreviousCursor() {
            return !! this.paginator.previousCursor
        },
        hasNextCursor() {
            return !! this.paginator.nextCursor
        },
        firstPage() {
            return this.paginator.firstPage
        },
        lastPage() {
            return this.paginator.lastPage
        },
        previousPage() {
            if (this.hasPreviousCursor()) {
                return this.setPage(this.previousCursor())
            }

            if (this.hasPreviousPage()) {
                component.$wire.call('previousPage', this.getPageName())
            }
        },
        nextPage() {
            if (this.hasNextCursor()) {
                return this.setPage(this.nextCursor())
            }

            if (this.hasNextPage()) {
                component.$wire.call('nextPage', this.getPageName())
            }
        },
        resetPage() {
            component.$wire.call('resetPage', this.getPageName())
        },
        setPage(page) {
            component.$wire.call('setPage', page, this.getCursorName() ?? this.getPageName())
        },
        previousCursor() {
            return this.paginator.previousCursor
        },
        nextCursor() {
            return this.paginator.nextCursor
        },
    })
}