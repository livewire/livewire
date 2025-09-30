
export class Island {
    constructor({ name, mode, origin }) {
        this.name = name
        this.mode = mode
        this.origin = origin
    }

    toMetadata() {
        return {
            island: { name: this.name, mode: this.mode },
        }
    }

    static closestIsland(origin) {
        let current = origin.el

        while (current) {
            // Check previous siblings
            let sibling = current.previousSibling;

            let foundEndMarker = []
            while (sibling) {
                if (Island.isEndMarker(sibling)) {
                    // Keep iterating up until we find the start marker and skip it...
                    foundEndMarker.push('a')
                }

                if (Island.isStartMarker(sibling)) {
                    if (foundEndMarker.length > 0) {
                        foundEndMarker.pop()
                    } else {
                        let key = Island.extractIslandName(sibling)

                        return new Island({ name: key, mode: 'replace', origin })
                    }
                }

                sibling = sibling.previousSibling;
            }

            // No start marker found at this level or found end marker
            // Go up to parent unless we've hit the component root
            current = current.parentElement;

            if (current && current.hasAttribute('wire:id')) {
                break; // Stop at component root
            }
        }

        return null;
    }

    static skipIslandContents(component, el, toEl, skipUntil) {
        if (Island.isStartMarker(el) && Island.isStartMarker(toEl)) {
            let key = Island.extractIslandName(toEl)
            let island = component.islands[key]
            let mode = island.mode
            let render = island.render

            if (['bypass', 'skip', 'once'].includes(render)) {
                skipUntil(node => Island.isEndMarker(node))
            } else if (mode === 'prepend') {
                // Collect all siblings until end marker
                let sibling = toEl.nextSibling
                let siblings = []
                while (sibling && !Island.isEndMarker(sibling)) {
                    siblings.push(sibling)
                    sibling = sibling.nextSibling
                }

                // Insert collected siblings after the start marker
                siblings.forEach(node => {
                    el.parentNode.insertBefore(node.cloneNode(true), el.nextSibling)
                })

                skipUntil(node => Island.isEndMarker(node))
            } else if (mode === 'append') {
                // Find end marker of fromEl
                let endMarker = el.nextSibling
                while (endMarker && !Island.isEndMarker(endMarker)) {
                    endMarker = endMarker.nextSibling
                }

                // Collect all siblings until end marker
                let sibling = toEl.nextSibling
                let siblings = []
                while (sibling && !Island.isEndMarker(sibling)) {
                    siblings.push(sibling)
                    sibling = sibling.nextSibling
                }

                // Insert collected siblings before the end marker
                siblings.forEach(node => {
                    endMarker.parentNode.insertBefore(node.cloneNode(true), endMarker)
                })

                skipUntil(node => Island.isEndMarker(node))
            }
        }
    }

    static isStartMarker(el) {
        return el.nodeType === 8 && el.textContent.startsWith('[if ISLAND')
    }

    static isEndMarker(el) {
        return el.nodeType === 8 && el.textContent.startsWith('[if ENDISLAND')
    }

    static extractIslandName(el) {
        let key = el.textContent.match(/\[if ISLAND:([\w-]+)(?::placeholder)?\]/)?.[1]

        return key
    }
}
