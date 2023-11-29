import { updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks, updateUrlAndStoreLatestHtmlForFutureBackButtons, whenTheBackOrForwardButtonIsClicked } from "./history"
import { getPretchedHtmlOr, prefetchHtml, storeThePrefetchedHtmlForWhenALinkIsClicked } from "./prefetch"
import { createUrlObjectFromString, extractDestinationFromLink, whenThisLinkIsHoveredFor, whenThisLinkIsPressed } from "./links"
import { packUpPersistedTeleports, removeAnyLeftOverStaleTeleportTargets, unPackPersistedTeleports } from "./teleport"
import { restoreScrollPositionOrScrollToTop, storeScrollInformationInHtmlBeforeNavigatingAway } from "./scroll"
import { putPersistantElementsBack, storePersistantElementsForLater } from "./persist"
import { finishAndHideProgressBar, showAndStartProgressBar } from "./bar"
import { swapCurrentPageWithNewHtml } from "./page"
import { fetchHtml } from "./fetch"
import Alpine from "alpinejs"

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

    Alpine.directive('navigate', (el, { value, expression, modifiers }, { evaluateLater, cleanup }) => {
        let shouldPrefetchOnHover = modifiers.includes('hover')

        shouldPrefetchOnHover && whenThisLinkIsHoveredFor(el, 60, () => {
            let destination = extractDestinationFromLink(el)

            prefetchHtml(destination, html => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination)
            })
        })

        whenThisLinkIsPressed(el, (whenItIsReleased) => {
            let destination = extractDestinationFromLink(el)

            prefetchHtml(destination, html => {
                storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination)
            })

            whenItIsReleased(() => {
                navigateTo(destination)
            })
        })
    })

    function navigateTo(destination) {
        showProgressBar && showAndStartProgressBar()

        fetchHtmlOrUsePrefetchedHtml(destination, html => {
            fireEventForOtherLibariesToHookInto('alpine:navigating')

            restoreScroll && storeScrollInformationInHtmlBeforeNavigatingAway()

            showProgressBar && finishAndHideProgressBar()

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

                    fireEventForOtherLibariesToHookInto('alpine:navigated')

                    updateUrlAndStoreLatestHtmlForFutureBackButtons(html, destination)

                    afterNewScriptsAreDoneLoading(() => {
                        andAfterAllThis(() => {
                            autofocus && autofocusElementsWithTheAutofocusAttribute()

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

        setTimeout(() => {
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
