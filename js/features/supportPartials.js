import { morphPartial } from '@/morph'
import { on } from '@/hooks'

on('effect', ({ component, effects }) => {
    let partials = effects.partials
    if (! partials) return

    partials.forEach(partial => {
        let { name, mode, content } = partial

        // Wrapping this in a double queueMicrotask. The first one puts it after all
        // other "effect" hooks, and the second one puts it after all reactive
        // Alpine effects (that are processed via flushJobs in scheduler).
        queueMicrotask(() => {
            queueMicrotask(() => {
                let { startNode, endNode } = findPartialComments(component.el, name)

                if (!startNode || !endNode) return

                let strippedContent = stripPartialComments(content, name)

                let parentElement = startNode.parentElement
                let parentElementTag = parentElement ? parentElement.tagName.toLowerCase() : 'div'

                if (mode === 'append') {
                    let container = document.createElement(parentElementTag)

                    container.innerHTML = strippedContent

                    // Insert each child node before the end node
                    Array.from(container.childNodes).forEach(node => {
                        endNode.parentNode.insertBefore(node, endNode)
                    })

                } else if (mode === 'prepend') {
                    let container = document.createElement(parentElementTag)

                    container.innerHTML = strippedContent

                    // Insert each child node after the start node in reverse order
                    // to maintain correct ordering
                    Array.from(container.childNodes)
                        .reverse()
                        .forEach(node => {
                            startNode.parentNode.insertBefore(node, startNode.nextSibling)
                        })
                } else {
                    morphPartial(component, startNode, endNode, strippedContent)
                }
            })
        })
    })
})

function stripPartialComments(content, partialName) {
    // Remove the start and end comment markers
    let startComment = `<!--[if PARTIAL:${partialName}]><![endif]-->`
    let endComment = `<!--[if ENDPARTIAL:${partialName}]><![endif]-->`

    // Strip out the comments from the content
    let stripped = content
        .replace(startComment, '')
        .replace(endComment, '')

    return stripped.trim()
}

function findPartialComments(rootEl, partialName) {
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
                if (node.textContent === `[if PARTIAL:${partialName}]><![endif]`) {
                    startNode = node
                }

                if (node.textContent === `[if ENDPARTIAL:${partialName}]><![endif]`) {
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
