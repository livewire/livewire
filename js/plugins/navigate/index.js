import { updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks, updateUrlAndStoreLatestHtmlForFutureBackButtons, whenTheBackOrForwardButtonIsClicked } from "./history"
import { getPretchedHtmlOr, prefetchHtml, storeThePrefetchedHtmlForWhenALinkIsClicked } from "./prefetch"
import { createUrlObjectFromString, extractDestinationFromLink, whenThisLinkIsHoveredFor, whenThisLinkIsPressed } from "./links"
import { isTeleportTarget, packUpPersistedTeleports, removeAnyLeftOverStaleTeleportTargets, unPackPersistedTeleports } from "./teleport"
import { restoreScrollPositionOrScrollToTop, storeScrollInformationInHtmlBeforeNavigatingAway } from "./scroll"
import { isPersistedElement, putPersistantElementsBack, storePersistantElementsForLater } from "./persist"
import { finishAndHideProgressBar, showAndStartProgressBar } from "./bar"
import { swapCurrentPageWithNewHtml } from "./page"
import { fetchHtml } from "./fetch"

let enablePersist = true
let showProgressBar = true
let restoreScroll = true
let autofocus = false

export default function (Alpine) {
    Alpine.navigate = (url) => {
        navigateTo(
            createUrlObjectFromString(url)
        )
    }

    Alpine.navigate.disableProgressBar = () => {
        showProgressBar = false
    }

    Alpine.addInitSelector(() => `[${Alpine.prefixed('navigate')}]`)

    Alpine.directive('navigate', (el, { modifiers }) => {
        let shouldPrefetchOnHover = modifiers.includes('hover')

        shouldPrefetchOnHover && whenThisLinkIsHoveredFor(el, 60, () => {
            let destination = extractDestinationFromLink(el)

            prefetchHtml(destination, (html, finalDestination) => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination)
            })
        })

        whenThisLinkIsPressed(el, (whenItIsReleased) => {
            let destination = extractDestinationFromLink(el)

            prefetchHtml(destination, (html, finalDestination) => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination)
            })

            whenItIsReleased(() => {
                const cancelableEvent = new CustomEvent('alpine:before-navigate', {
                    cancelable: true,
                    bubbles: true,
                    detail: {
                        url: destination.href
                    }
                })

                document.dispatchEvent(cancelableEvent) // Dispatch cancelable event
                if (!cancelableEvent.defaultPrevented) {
                    navigateTo(destination)
                }
            })
        })
    })

    function navigateTo(destination) {
        showProgressBar && showAndStartProgressBar()

        fetchHtmlOrUsePrefetchedHtml(destination, (html, finalDestination) => {
            fireEventForOtherLibariesToHookInto('alpine:navigating')

            restoreScroll && storeScrollInformationInHtmlBeforeNavigatingAway()

            showProgressBar && finishAndHideProgressBar()

            cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement()

            updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks()

            preventAlpineFromPickingUpDomChanges(Alpine, andAfterAllThis => {
                enablePersist && storePersistantElementsForLater(persistedEl => {
                    packUpPersistedTeleports(persistedEl)
                })

                swapCurrentPageWithNewHtml(html, (afterNewScriptsAreDoneLoading) => {
                    removeAnyLeftOverStaleTeleportTargets(document.body)

                    enablePersist && putPersistantElementsBack((persistedEl, newStub) => {
                        unPackPersistedTeleports(persistedEl)
                    })

                    restoreScrollPositionOrScrollToTop()

                    updateUrlAndStoreLatestHtmlForFutureBackButtons(html, finalDestination)

                    fireEventForOtherLibariesToHookInto('alpine:navigated')

                    afterNewScriptsAreDoneLoading(() => {
                        andAfterAllThis(() => {
                            setTimeout(() => {
                                autofocus && autofocusElementsWithTheAutofocusAttribute()
                            })

                            nowInitializeAlpineOnTheNewPage(Alpine)
                        })
                    })
                })
            })
        })
    }

    whenTheBackOrForwardButtonIsClicked((html) => {
        // @todo: see if there's a way to update the current HTML BEFORE
        // the back button is hit, and not AFTER:
        storeScrollInformationInHtmlBeforeNavigatingAway()
        // updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks()

        preventAlpineFromPickingUpDomChanges(Alpine, andAfterAllThis => {
            enablePersist && storePersistantElementsForLater(persistedEl => {
                packUpPersistedTeleports(persistedEl)
            })

            swapCurrentPageWithNewHtml(html, () => {
                removeAnyLeftOverStaleTeleportTargets(document.body)

                enablePersist && putPersistantElementsBack((persistedEl, newStub) => {
                    unPackPersistedTeleports(persistedEl)
                })

                restoreScrollPositionOrScrollToTop()

                fireEventForOtherLibariesToHookInto('alpine:navigated')

                andAfterAllThis(() => {
                    autofocus && autofocusElementsWithTheAutofocusAttribute()

                    nowInitializeAlpineOnTheNewPage(Alpine)
                })
            })

        })
    })

    // Because DOMContentLoaded is fired on first load,
    // we should fire alpine:navigated as a replacement as well...
    setTimeout(() => {
        fireEventForOtherLibariesToHookInto('alpine:navigated')
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

function fireEventForOtherLibariesToHookInto(eventName) {
    document.dispatchEvent(new CustomEvent(eventName, { bubbles: true }))
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
