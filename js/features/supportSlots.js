import { findComponent } from '@/store'
import { morphFragment } from '@/morph'
import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    let slots = effects.slots
    if (! slots) return

    let parentId = component.el.getAttribute('wire:id')

    Object.entries(slots).forEach(([childId, childSlots]) => {
        let childComponent = findComponent(childId)

        if (! childComponent) return

        Object.entries(childSlots).forEach(([name, content]) => {
            // Wrapping this in a double queueMicrotask. The first one puts it after all
            // other "effect" hooks, and the second one puts it after all reactive
            // Alpine effects (that are processed via flushJobs in scheduler).
            queueMicrotask(() => {
                queueMicrotask(() => {
                    queueMicrotask(() => {
                        let fullName = parentId ? `${name}:${parentId}` : name

                        let { startNode, endNode } = findSlotComments(childComponent.el, fullName)

                        if (!startNode || !endNode) return

                        let strippedContent = stripSlotComments(content, fullName)

                        // Use morphFragment to replace the content between slot markers
                        morphFragment(childComponent, startNode, endNode, strippedContent)
                    })
                })
            })
        })
    })
})

function stripSlotComments(content, slotName) {
    // Remove the start and end comment markers
    let startComment = `<!--[if SLOT:${slotName}]><![endif]-->`
    let endComment = `<!--[if ENDSLOT:${slotName}]><![endif]-->`

    // Strip out the comments from the content
    let stripped = content
        .replace(startComment, '')
        .replace(endComment, '')

    return stripped.trim()
}

function findSlotComments(rootEl, slotName) {
    let startNode = null
    let endNode = null

    walkElements(rootEl, (el, skip) => {
        // Skip nested Livewire components
        if (el.hasAttribute && el.hasAttribute('wire:id') && el !== rootEl) {
            return skip()
        }

        // Check all child nodes (including text and comment nodes)
        Array.from(el.childNodes).forEach(node => {
            if (node.nodeType === Node.COMMENT_NODE) {
                if (node.textContent === `[if SLOT:${slotName}]><![endif]`) {
                    startNode = node
                }

                if (node.textContent === `[if ENDSLOT:${slotName}]><![endif]`) {
                    endNode = node
                }
            }
        })
    })

    return { startNode, endNode }
}

function walkElements(el, callback) {
    let skip = false
    callback(el, () => skip = true)

    if (skip) return

    Array.from(el.children).forEach(child => {
        walkElements(child, callback)
    })
}

export function skipSlotContents(el, toEl, skipUntil) {
    if (isStartMarker(el) && isStartMarker(toEl)) {
        skipUntil(node => isEndMarker(node))
    }
}

function isStartMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if SLOT')
}

function isEndMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ENDSLOT')
}

export function extractSlotData(el) {
    let regex = /\[if SLOT:(\w+)(?::(\w+))?\]/
    let match = el.textContent.match(regex)

    if (! match) return

    return {
        name: match[1],
        parentId: match[2] || null
    }
}

export function checkPreviousSiblingForSlotStartMarker(el) {
    function searchInPreviousSiblings(node) {
        let sibling = node.previousSibling

        while (sibling) {
            if (isEndMarker(sibling)) {
                return null
            }

            if (isStartMarker(sibling)) {
                return sibling
            }
            sibling = sibling.previousSibling
        }

        return null
    }

    function searchRecursively(currentEl) {
        // First check previous siblings of current element
        let found = searchInPreviousSiblings(currentEl)
        if (found !== null) {
            return found
        }

        // If nothing found and we have a parent, check if parent is another Livewire component
        let parent = currentEl.parentElement
        if (!parent) {
            return null
        }

        // Stop if we encounter another Livewire component
        if (parent.hasAttribute && parent.hasAttribute('wire:id')) {
            return null
        }

        // Recursively search up the tree
        return searchRecursively(parent)
    }

    return searchRecursively(el)
}
