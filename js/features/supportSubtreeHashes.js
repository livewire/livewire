import { on } from '@/hooks'

// Keyed elements carry a `wire:hash` of the markup the server rendered for them. Requests send
// the hashes currently in the DOM, and the server answers with an empty `wire:same` placeholder
// for any subtree that hasn't changed, which morphing then skips entirely...

// Per component: hash => the untouched server-rendered element it came from, used to rebuild a
// placeholder when morphing can't simply keep the element in place (e.g. reordered keys)...
let sources = new WeakMap

// Only components the server hashes for do any of this, so apps without it pay nothing...
function enabled(component) {
    return component?.snapshot?.memo?.hashes === true
}

// A `wire:same` without a hash is someone's own `wire:same` event listener, not a placeholder...
function isPlaceholder(el) {
    return el.hasAttribute('wire:same') && el.hasAttribute('wire:hash')
}

export function collectSubtreeHashes(component) {
    if (! enabled(component)) return ''

    // A resync asks for everything...
    if (component.__subtreeHashesDisabledOnce) {
        component.__subtreeHashesDisabledOnce = false

        return ''
    }

    // Streamed content changes behind the morph's back, so don't claim anything around it...
    let tainted = new Set

    component.el.querySelectorAll('[wire\\:stream],[wire\\:stream\\.replace]').forEach(el => {
        for (let parent = el; parent && parent !== component.el; parent = parent.parentElement) tainted.add(parent)
    })

    let hashes = new Set

    keyedDescendants(component.el).forEach(el => {
        if (tainted.has(el) || el.closest('[wire\\:stream],[wire\\:stream\\.replace]')) return

        hashes.add(el.getAttribute('wire:hash'))
    })

    // Fixed-width hashes, so they travel as one string...
    return Array.from(hashes).join('')
}

// Code that changes a component's server-rendered markup outside Livewire's own morph (a
// package morphing its own partials, say) must report it, or the old markup would still
// be claimed for that element and everything around it...
export function markChanged(el) {
    if (! el?.querySelectorAll) return

    el.querySelectorAll('[wire\\:hash]').forEach(i => i.removeAttribute('wire:hash'))

    forgetHashesAround(el)
}

// The keyed elements holding a node no longer hold the markup the server sent for them...
export function forgetHashesAround(node) {
    for (let el = node instanceof Element ? node : node?.parentElement; el && ! el.hasAttribute('wire:id'); el = el.parentElement) {
        el.removeAttribute('wire:hash')
    }
}

// The configs Livewire's own morphs run with, so they can be told apart from morphs other code
// runs through Alpine (even from inside one of Livewire's morph hooks). Kept here rather than in
// the morph module, which this one can't import without a cycle through the request module...
let livewireMorphs = new WeakSet

export function rememberLivewireMorph(config) {
    livewireMorphs.add(config)

    return config
}

function isLivewireMorph(options) {
    return livewireMorphs.has(options)
}

// Packages often morph a component's markup themselves through Alpine (Filament's partials, for
// one), so report those morphs for them. This overrides Alpine's own functions for now, until
// Alpine's morph can report morphs it runs on behalf of other code itself...
export function reportMorphsOutsideLivewire(Alpine) {
    let { morph, morphBetween } = Alpine

    Alpine.morph = (from, toHtml, options) => {
        if (isLivewireMorph(options)) return morph(from, toHtml, options)

        let container = from.parentElement

        markChanged(from)

        let result = morph(from, toHtml, options)

        // ...and again, in case the new markup came from an earlier render. Where the morph
        // replaced the element itself, what took its place is only reachable from its parent...
        markChanged(from.isConnected ? from : container)

        return result
    }

    Alpine.morphBetween = (startMarker, endMarker, toHtml, options) => {
        if (isLivewireMorph(options)) return morphBetween(startMarker, endMarker, toHtml, options)

        let container = startMarker.parentElement

        markChanged(container)

        let result = morphBetween(startMarker, endMarker, toHtml, options)

        markChanged(container)

        return result
    }
}

// Right before and right after Alpine morphs, with nothing able to run in between, so a
// morph's bookkeeping can't be mixed up with another one's...
on('morph.starting', ({ toEl, component, island }) => beginMorph(component, toEl, island))

// ...while moved elements can still be put back without Alpine tearing them down as removed...
on('morph.finished', ({ component }) => finishMorph(component))

on('island.morphed', ({ startNode }) => {
    // ...and the keyed elements around an island now hold markup the server didn't send...
    forgetHashesAround(startNode)
})

// An island only brings part of the markup, so it adds to what's stored instead of replacing it...
function beginMorph(component, toEl, island) {
    if (! enabled(component)) return

    let partial = island !== undefined
    let previous = sources.get(component) ?? new Map
    let next = partial ? new Map(previous) : new Map

    let retain = (hash, node, force = false) => {
        if (! node || (! force && next.has(hash))) return false

        next.set(hash, node)

        return true
    }

    // A placeholder stands for stored markup, which may itself hold placeholders. Each stored
    // element is scanned once: the keyed elements in it are retained as they are...
    let retainStored = hash => {
        let node = previous.get(hash)

        if (! retain(hash, node)) return

        keyedDescendants(node).forEach(el => {
            let nested = el.getAttribute('wire:hash')

            isPlaceholder(el) ? retainStored(nested) : retain(nested, el)
        })
    }

    let wanted = new Set

    // ...as are the incoming ones, which this single pass already visits at every depth...
    keyedDescendants(toEl).forEach(el => {
        let hash = el.getAttribute('wire:hash')

        if (isPlaceholder(el)) {
            wanted.add(hash)
            retainStored(hash)
        } else {
            retain(hash, el, partial)
        }
    })

    sources.set(component, next)

    // The live elements those placeholders stand for, in case morphing re-adds instead of moving...
    let originals = new Map

    if (wanted.size) keyedDescendants(component.el).forEach(el => {
        let hash = el.getAttribute('wire:hash')

        if (wanted.has(hash) && ! originals.has(hash)) originals.set(hash, el)
    })

    component.__subtreeMorph = { originals, placed: [], island }
}

function finishMorph(component) {
    let state = component.__subtreeMorph

    if (! state) return

    delete component.__subtreeMorph

    state.placed.forEach(placeholder => {
        let original = state.originals.get(placeholder.getAttribute('wire:hash'))

        if (original && original !== placeholder && ! original.contains(placeholder)) {
            placeholder.replaceWith(original)

            return
        }

        // Nothing to put there, so it must never be claimed as up to date...
        placeholder.removeAttribute('wire:hash')
        placeholder.removeAttribute('wire:same')

        component.__subtreeHashesNeedResync = true
    })

    // Detach stored elements from the discarded "to" tree so it can be garbage collected...
    sources.get(component)?.forEach(node => {
        if (node.parentElement && ! node.parentElement.closest('[wire\\:hash]')) node.remove()
    })

    if (! component.__subtreeHashesNeedResync) return

    component.__subtreeHashesNeedResync = false
    component.__subtreeHashesDisabledOnce = true

    sources.delete(component)

    // A component's own render leaves its islands alone, so a broken island renders itself...
    state.island === undefined
        ? component.$wire.$refresh()
        : component.$wire.$island(state.island).$refresh()
}

on('morph.updating', ({ el, toEl, component, skip }) => {
    if (! enabled(component)) return

    if (! isPlaceholder(toEl)) {
        // Keep the hash in step with the markup this element is morphed against, even
        // where its own attributes aren't patched (e.g. `wire:ignore.self`)...
        if (toEl.hasAttribute('wire:hash')) el.setAttribute('wire:hash', toEl.getAttribute('wire:hash'))
        else el.removeAttribute('wire:hash')

        return
    }

    if (el.getAttribute('wire:hash') === toEl.getAttribute('wire:hash')) return skip()

    // Matched against a different element, so morph it from the stored markup instead...
    if (fill(toEl, component)) return

    // Without the markup, keep what's there and ask for a full render...
    component.__subtreeHashesNeedResync = true

    skip()
})

// Morphing sometimes re-adds a keyed element instead of moving it (and drops the original),
// so a placeholder added that way gets the original moved into its place afterwards...
on('morph.adding', ({ el, component }) => {
    if (! enabled(component) || ! el.hasAttribute) return

    let placeholders = isPlaceholder(el) ? [el] : []

    el.querySelectorAll('[wire\\:same][wire\\:hash]').forEach(i => placeholders.push(i))

    placeholders.forEach(placeholder => {
        if (component.__subtreeMorph?.originals.has(placeholder.getAttribute('wire:hash'))) return

        if (fill(placeholder, component)) return

        // It goes in empty until the resync, so it must never be claimed as up to date...
        placeholder.removeAttribute('wire:hash')
        placeholder.removeAttribute('wire:same')

        component.__subtreeHashesNeedResync = true
    })
})

on('morph.added', ({ el, component }) => {
    let state = component?.__subtreeMorph

    if (! state || ! el.hasAttribute) return

    if (isPlaceholder(el)) state.placed.push(el)

    el.querySelectorAll('[wire\\:same][wire\\:hash]').forEach(i => state.placed.push(i))
})

function fill(placeholder, component) {
    let source = sources.get(component)?.get(placeholder.getAttribute('wire:hash'))

    if (! source) return false

    Array.from(placeholder.attributes).forEach(attr => placeholder.removeAttribute(attr.name))
    Array.from(source.attributes).forEach(attr => placeholder.setAttributeNode(attr.cloneNode()))

    placeholder.replaceChildren(...Array.from(source.childNodes, node => node.cloneNode(true)))

    return keyedDescendants(placeholder)
        .filter(el => isPlaceholder(el))
        .every(el => fill(el, component))
}

function keyedDescendants(node) {
    return Array.from(node.querySelectorAll('[wire\\:hash]')).filter(el => {
        for (let parent = el.parentElement; parent && parent !== node; parent = parent.parentElement) {
            if (parent.hasAttribute('wire:id')) return false
        }

        return true
    })
}
