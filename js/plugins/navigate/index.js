import { replaceUrl, updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks, updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks, updateUrlAndStoreLatestHtmlForFutureBackButtons, whenTheBackOrForwardButtonIsClicked } from "./history"
import { getPretchedHtmlOr, prefetchHtml, storeThePrefetchedHtmlForWhenALinkIsClicked } from "./prefetch"
import { createUrlObjectFromString, extractDestinationFromLink, whenThisLinkIsHoveredFor, whenThisLinkIsPressed } from "./links"
import { isTeleportTarget, packUpPersistedTeleports, removeAnyLeftOverStaleTeleportTargets, unPackPersistedTeleports } from "./teleport"
import { restoreScrollPositionOrScrollToTop, storeScrollInformationInHtmlBeforeNavigatingAway } from "./scroll"
import { isPersistedElement, putPersistantElementsBack, storePersistantElementsForLater } from "./persist"
import { finishAndHideProgressBar, removeAnyLeftOverStaleProgressBars, showAndStartProgressBar } from "./bar"
import { packUpPersistedPopovers, unPackPersistedPopovers } from "./popover"
import { swapCurrentPageWithNewHtml } from "./page"
import { fetchHtml } from "./fetch"

let enablePersist = true
let showProgressBar = true
let restoreScroll = true
let autofocus = false

export default function (Alpine) {

    Alpine.navigate = (url, options = {}) => {
        let { preserveScroll = false } = options

        let destination = createUrlObjectFromString(url)

        let prevented = fireEventForOtherLibrariesToHookInto('alpine:navigate', {
            url: destination, history: false, cached: false,
         })

        if (prevented) return

        navigateTo(destination, { preserveScroll })
    }

    Alpine.navigate.disableProgressBar = () => {
        showProgressBar = false
    }

    Alpine.addInitSelector(() => `[${Alpine.prefixed('navigate')}]`)

    Alpine.directive('navigate', (el, { modifiers }) => {
        let shouldPrefetchOnHover = modifiers.includes('hover')

        let preserveScroll = modifiers.includes('preserve-scroll')

        shouldPrefetchOnHover && whenThisLinkIsHoveredFor(el, 60, () => {
            let destination = extractDestinationFromLink(el)

            if (! destination) return

            prefetchHtml(destination, (html, finalDestination) => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination)
            })
        })

        whenThisLinkIsPressed(el, (whenItIsReleased) => {
            let destination = extractDestinationFromLink(el)

            if (! destination) return

            prefetchHtml(destination, (html, finalDestination) => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination)
            })

            whenItIsReleased(() => {
                let prevented = fireEventForOtherLibrariesToHookInto('alpine:navigate', {
                    url: destination, history: false, cached: false,
                 })

                if (prevented) return

                navigateTo(destination, { preserveScroll })
            })
        })
    })

    function navigateTo(destination, { preserveScroll = false, shouldPushToHistoryState = true }) {
        showProgressBar && showAndStartProgressBar()

        fetchHtmlOrUsePrefetchedHtml(destination, (html, finalDestination) => {
            // Fire the navigating event, allowing listeners to register onSwap callbacks
            let swapCallbacks = []

            fireEventForOtherLibrariesToHookInto('alpine:navigating', {
                onSwap: (callback) => swapCallbacks.push(callback)
            })

            restoreScroll && storeScrollInformationInHtmlBeforeNavigatingAway()

            cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement()

            updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks()

            preventAlpineFromPickingUpDomChanges(Alpine, andAfterAllThis => {
                enablePersist && storePersistantElementsForLater(persistedEl => {
                    packUpPersistedTeleports(persistedEl)
                    packUpPersistedPopovers(persistedEl)
                })

                if (shouldPushToHistoryState) {
                    updateUrlAndStoreLatestHtmlForFutureBackButtons(html, finalDestination)
                } else {
                    replaceUrl(finalDestination, html)
                }

                swapCurrentPageWithNewHtml(html, (afterNewScriptsAreDoneLoading) => {
                    removeAnyLeftOverStaleTeleportTargets(document.body)

                    enablePersist && putPersistantElementsBack((persistedEl, newStub) => {
                        unPackPersistedTeleports(persistedEl)
                        unPackPersistedPopovers(persistedEl)
                    })

                    !preserveScroll && restoreScrollPositionOrScrollToTop()

                    // Invoke any callbacks registered via onSwap during the navigating event
                    swapCallbacks.forEach(callback => callback())

                    afterNewScriptsAreDoneLoading(() => {
                        andAfterAllThis(() => {
                            setTimeout(() => {
                                autofocus && autofocusElementsWithTheAutofocusAttribute()
                            })

                            nowInitializeAlpineOnTheNewPage(Alpine)

                            fireEventForOtherLibrariesToHookInto('alpine:navigated')
                            showProgressBar && finishAndHideProgressBar()
                        })
                    })
                })
            })
        })
    }

    whenTheBackOrForwardButtonIsClicked(
        (ifThePageBeingVisitedHasntBeenCached) => {
            ifThePageBeingVisitedHasntBeenCached((url) => {
                let destination = createUrlObjectFromString(url)

                let prevented = fireEventForOtherLibrariesToHookInto('alpine:navigate', {
                    url: destination, history: true, cached: false,
                 })

                if (prevented) return

                navigateTo(destination, { shouldPushToHistoryState: false })
            })
        },
        (html, url, currentPageUrl, currentPageKey) => {
            let destination = createUrlObjectFromString(url)

            let prevented = fireEventForOtherLibrariesToHookInto('alpine:navigate', {
                url: destination, history: true, cached: true,
            })

            if (prevented) return

            // @todo: see if there's a way to update the current HTML BEFORE
            // the back button is hit, and not AFTER:
            storeScrollInformationInHtmlBeforeNavigatingAway()

            // Fire the navigating event, allowing listeners to register onSwap callbacks
            let swapCallbacks = []

            fireEventForOtherLibrariesToHookInto('alpine:navigating', {
                onSwap: (callback) => swapCallbacks.push(callback)
            })

            // Update the snapshot (not the history state, as the history state has
            // already changed to the new page due to the popstate event).
            // This ensures the current HTML has the latest snapshot.
            updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks(currentPageUrl, currentPageKey)

            preventAlpineFromPickingUpDomChanges(Alpine, andAfterAllThis => {
                enablePersist && storePersistantElementsForLater(persistedEl => {
                    packUpPersistedTeleports(persistedEl)
                    packUpPersistedPopovers(persistedEl)
                })

                swapCurrentPageWithNewHtml(html, () => {
                    removeAnyLeftOverStaleProgressBars()

                    removeAnyLeftOverStaleTeleportTargets(document.body)

                    enablePersist && putPersistantElementsBack((persistedEl, newStub) => {
                        unPackPersistedTeleports(persistedEl)
                        unPackPersistedPopovers(persistedEl)
                    })

                    restoreScrollPositionOrScrollToTop()

                    // Invoke any callbacks registered via onSwap during the navigating event
                    swapCallbacks.forEach(callback => callback())

                    andAfterAllThis(() => {
                        autofocus && autofocusElementsWithTheAutofocusAttribute()

                        nowInitializeAlpineOnTheNewPage(Alpine)

                        fireEventForOtherLibrariesToHookInto('alpine:navigated')
                    })
                })
            })
        },
    )

    // Because DOMContentLoaded is fired on first load,
    // we should fire alpine:navigated as a replacement as well...
    setTimeout(() => {
        fireEventForOtherLibrariesToHookInto('alpine:navigated')
    })
}

function fetchHtmlOrUsePrefetchedHtml(fromDestination, callback) {
    getPretchedHtmlOr(fromDestination, callback, () => {
        fetchHtml(fromDestination, callback)
    })
}

function preventAlpineFromPickingUpDomChanges(Alpine, callback) {
    Alpine.stopObservingMutations()

    callback((afterAllThis) => {
        Alpine.startObservingMutations()

        queueMicrotask(() => {
            afterAllThis()
        })
    })
}

function fireEventForOtherLibrariesToHookInto(name, detail) {
    let event = new CustomEvent(name, {
        cancelable: true,
        bubbles: true,
        detail,
    })

    document.dispatchEvent(event)

    return event.defaultPrevented
}

function nowInitializeAlpineOnTheNewPage(Alpine) {
    Alpine.initTree(document.body, undefined, (el, skip) => {
        if (el._x_wasPersisted) skip()
    })
}

function autofocusElementsWithTheAutofocusAttribute() {
    document.querySelector('[autofocus]') && document.querySelector('[autofocus]').focus()
}

function cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement() {
    // Create a new tree walker that skips persisted elements...
    let walker = function (root, callback) {
        Alpine.walk(root, (el, skip) => {
            if (isPersistedElement(el)) skip()
            if (isTeleportTarget(el)) skip()
            else callback(el, skip)
        })
    }

    // Set Alpine in motion to destroy itself on the page. If this proves
    // to be a performance issue at some point (walking the DOM tree),
    // we can be more surgical about cleaning up x-for/if instead...
    Alpine.destroyTree(document.body, walker)
}
