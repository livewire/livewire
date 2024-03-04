let oldBodyScriptTagHashes = []

let attributesExemptFromScriptTagHashing = [
    'data-csrf',
    'aria-hidden',
]

export function swapCurrentPageWithNewHtml(html, andThen) {
    let newDocument = (new DOMParser()).parseFromString(html, "text/html")
    let newBody = document.adoptNode(newDocument.body)
    let newHead = document.adoptNode(newDocument.head)

    oldBodyScriptTagHashes = oldBodyScriptTagHashes.concat(Array.from(document.body.querySelectorAll('script')).map(i => {
        return simpleHash(ignoreAttributes(i.outerHTML, attributesExemptFromScriptTagHashing))
    }))

    let afterRemoteScriptsHaveLoaded = () => {}

    mergeNewHead(newHead).finally(() => {
        afterRemoteScriptsHaveLoaded()
    })

    prepNewBodyScriptTagsToRun(newBody, oldBodyScriptTagHashes)

    let oldBody = document.body

    document.body.replaceWith(newBody)

    Alpine.destroyTree(oldBody)

    andThen(i => afterRemoteScriptsHaveLoaded = i)
}

function prepNewBodyScriptTagsToRun(newBody, oldBodyScriptTagHashes) {
    newBody.querySelectorAll('script').forEach(i => {
        // We don't want to re-run script tags marked as "data-navigate-once"...
        if (i.hasAttribute('data-navigate-once')) {
            // However, if they didn't exist on the previous page, we do.
            // Therefore, we'll check the "old body script hashes" to
            // see if it was already there before skipping it...

            let hash = simpleHash(
                ignoreAttributes(i.outerHTML, attributesExemptFromScriptTagHashing)
            )

            if (oldBodyScriptTagHashes.includes(hash)) return
        }

        i.replaceWith(cloneScriptTag(i))
    })
}

function mergeNewHead(newHead) {
    let children = Array.from(document.head.children)
    let headChildrenHtmlLookup = children.map(i => i.outerHTML)

    // Only add scripts and styles that aren't already loaded on the page.
    let garbageCollector = document.createDocumentFragment()

    let touchedHeadElements = []

    let remoteScriptsPromises = []

    for (let child of Array.from(newHead.children)) {
        if (isAsset(child)) {
            if (! headChildrenHtmlLookup.includes(child.outerHTML)) {
                if (isTracked(child)) {
                    if (ifTheQueryStringChangedSinceLastRequest(child, children)) {
                        setTimeout(() => window.location.reload())
                    }
                }

                if (isScript(child)) {
                    try {
                        remoteScriptsPromises.push(
                            injectScriptTagAndWaitForItToFullyLoad(
                                cloneScriptTag(child)
                            )
                        )
                    } catch (error) {
                        // Let's eat any promise rejects so that we don't
                        // break the rest of the Alpine intializing...
                        // Any errors triggered by adding the script tag to the page
                        // will still be thrown...
                    }
                } else {
                    document.head.appendChild(child)
                }
            } else {
                garbageCollector.appendChild(child)
            }

            touchedHeadElements.push(child)
        }
    }

    // Remove any assets that aren't on the new page...
    // @todo: Re-enable this code and find a better way to managed injected stylesheets. See livewire/livewire#6824
    // for (let child of Array.from(document.head.children)) {
    //     if (isAsset(child)) {
    //         if (! touchedHeadElements.some(i => i.outerHTML === child.outerHTML)) {
    //             child.remove()
    //         }
    //     }
    // }

    // How to free up the garbage collector?

    // Remove existing non-asset elements like meta, base, title, template.
    for (let child of Array.from(document.head.children)) {
        if (! isAsset(child)) child.remove()
    }

    // Add new non-asset elements left over in the new head element.
    for (let child of Array.from(newHead.children)) {
        document.head.appendChild(child)
    }

    return Promise.all(remoteScriptsPromises)
}

async function injectScriptTagAndWaitForItToFullyLoad(script) {
    return new Promise((resolve, reject) => {
        // Script assets need to be loaded synchronously so that scripts have
        // their global variables available...
        if (script.src) {
            script.onload = () => resolve()
            script.onerror = () => reject()
        } else {
            resolve()
        }

        document.head.appendChild(script)
    })
}

function cloneScriptTag(el) {
    let script = document.createElement('script')

    script.textContent = el.textContent
    script.async = el.async

    for (let attr of el.attributes) {
        script.setAttribute(attr.name, attr.value)
    }

    return script
}

function isTracked(el) {
    return el.hasAttribute('data-navigate-track')
}

function ifTheQueryStringChangedSinceLastRequest(el, currentHeadChildren) {
    let [uri, queryString] = extractUriAndQueryString(el)

    return currentHeadChildren.some(child => {
        if (! isTracked(child)) return false

        let [currentUri, currentQueryString] = extractUriAndQueryString(child)

        // Only consider a data-navigate-track element changed if the query string has changed (not the URI)...
        if (currentUri === uri && queryString !== currentQueryString) return true
    })
}

function extractUriAndQueryString(el) {
    let url = isScript(el) ? el.src : el.href

    return url.split('?')
}

function isAsset(el) {
    return (el.tagName.toLowerCase() === 'link' && el.getAttribute('rel').toLowerCase() === 'stylesheet')
        || el.tagName.toLowerCase() === 'style'
        || el.tagName.toLowerCase() === 'script'
}

function isScript(el)   {
    return el.tagName.toLowerCase() === 'script'
}

function simpleHash(str) {
    return str.split('').reduce((a, b) => {
        a = ((a << 5) - a) + b.charCodeAt(0)

        return a & a
    }, 0);
}

function ignoreAttributes(subject, attributesToRemove) {
    let result = subject

    attributesToRemove.forEach(attr => {
        // Create a regex pattern to match the attribute and its value.
        // The regex handles attributes that have values surrounded by either single or double quotes.
        const regex = new RegExp(`${attr}="[^"]*"|${attr}='[^']*'`, 'g')

        result = result.replace(regex, '')
    })

    // Remove all whitespace to make things less flaky...
    result = result.replaceAll(' ', '')

    return result.trim()
}
