import { replaceUrl, updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks, updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks, updateUrlAndStoreLatestHtmlForFutureBackButtons, whenTheBackOrForwardButtonIsClicked } from "./history"
import { getPretchedHtmlOr, prefetchHtml, storeThePrefetchedHtmlForWhenALinkIsClicked } from "./prefetch"
import { createUrlObjectFromString, extractDestinationFromLink, isSameOrigin, linkShouldBeHandledNatively, visitNatively, whenThisLinkIsHoveredFor, whenThisLinkIsPressed } from "./links"
import { isTeleportTarget, packUpPersistedTeleports, removeAnyLeftOverStaleTeleportTargets, unPackPersistedTeleports } from "./teleport"
import { restoreScrollPositionOrScrollToTop, storeScrollInformationInHtmlBeforeNavigatingAway } from "./scroll"
import { isPersistedElement, putPersistantElementsBack, storePersistantElementsForLater } from "./persist"
import { finishAndHideProgressBar, removeAnyLeftOverStaleProgressBars, showAndStartProgressBar } from "./bar"
import { packUpPersistedPopovers, unPackPersistedPopovers } from "./popover"
import { transitionPageSwap } from "./transition"
import { swapCurrentPageWithNewHtml } from "./page"
import { fetchHtml } from "./fetch"
import { startNavigation } from "./navigation"

let enablePersist = true
let showProgressBar = true
let restoreScroll = true

export default function (Alpine) {

    Alpine.navigate = (url, options = {}) => {
        let { preserveScroll = false } = options

        let destination = createUrlObjectFromString(url)

        // Navigating swaps the new page into the current document, so it only
        // applies to same-origin destinations. Anything else gets handed off
        // to the browser as a normal, full page visit...
        if (! isSameOrigin(destination)) return visitNatively(destination)

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

            if (linkShouldBeHandledNatively(el, destination)) return

            prefetchHtml(destination, (html, finalDestination) => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination)
            }, () => {
                showProgressBar && finishAndHideProgressBar()
            })
        })

        whenThisLinkIsPressed(el, (whenItIsReleased) => {
            let destination = extractDestinationFromLink(el)

            prefetchHtml(destination, (html, finalDestination) => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination)
            }, () => {
                showProgressBar && finishAndHideProgressBar()
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
        let navigation = startNavigation()

        showProgressBar && showAndStartProgressBar()

        fetchHtmlOrUsePrefetchedHtml(destination, (html, finalDestination) => {
            // The request may have been redirected off to another origin. We can't
            // swap that page into this document, so let the browser visit it...
            if (! isSameOrigin(finalDestination)) {
                navigation.ready()
                navigation.finish()

                return visitNatively(finalDestination)
            }

            navigation.ready()

            // Fire the navigating event, allowing listeners to register onSwap callbacks
            let swapCallbacks = []

            fireEventForOtherLibrariesToHookInto('alpine:navigating', {
                onSwap: (callback) => swapCallbacks.push(callback)
            })

            restoreScroll && storeScrollInformationInHtmlBeforeNavigatingAway()

            cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement()

            // Only update the current page's history state if we're pushing to history.
            // For popstate-triggered navigations (shouldPushToHistoryState = false),
            // the history state has already changed and we shouldn't overwrite it.
            shouldPushToHistoryState && updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks()

            preventAlpineFromPickingUpDomChanges(Alpine, andAfterAllThis => {
                transitionPageSwap(html, () => {
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
                                nowInitializeAlpineOnTheNewPage(Alpine)
                                autofocusElementsWithTheAutofocusAttribute()

                                fireEventForOtherLibrariesToHookInto('alpine:navigated')
                                navigation.finish()
                                showProgressBar && finishAndHideProgressBar()
                            })
                        })
                    })
                })
            })
        }, (error) => {
            navigation.cancel()

            showProgressBar && finishAndHideProgressBar()

            // We cancelled this request ourselves, so the user is already on their
            // way somewhere else. Sending them to a destination they abandoned
            // would be worse than doing nothing...
            if (requestWasCancelled(error)) return

            // A rejected fetch doesn't always mean the network is gone — following a
            // redirect to another origin fails the same way, and we deliberately stay
            // put for those. Only when the browser tells us we're offline do we hand
            // the navigation back, so the user gets the browser's own offline page
            // instead of a click that silently did nothing...
            if (navigator.onLine) return

            window.location.href = destination.href
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

            let navigation = startNavigation()

            // @todo: see if there's a way to update the current HTML BEFORE
            // the back button is hit, and not AFTER:
            storeScrollInformationInHtmlBeforeNavigatingAway()

            // Fire the navigating event, allowing listeners to register onSwap callbacks
            let swapCallbacks = []

            navigation.ready()

            fireEventForOtherLibrariesToHookInto('alpine:navigating', {
                onSwap: (callback) => swapCallbacks.push(callback)
            })

            cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement()

            // Update the snapshot (not the history state, as the history state has
            // already changed to the new page due to the popstate event).
            // This ensures the current HTML has the latest snapshot.
            updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks(currentPageKey, currentPageUrl)

            preventAlpineFromPickingUpDomChanges(Alpine, andAfterAllThis => {
                transitionPageSwap(html, () => {
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
                            nowInitializeAlpineOnTheNewPage(Alpine)
                            autofocusElementsWithTheAutofocusAttribute()

                            fireEventForOtherLibrariesToHookInto('alpine:navigated')
                            navigation.finish()
                        })
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

function fetchHtmlOrUsePrefetchedHtml(fromDestination, callback, errorCallback) {
    getPretchedHtmlOr(fromDestination, callback, () => {
        fetchHtml(fromDestination, callback, errorCallback)
    })
}

function requestWasCancelled(error) {
    return error?.name === 'AbortError'
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
