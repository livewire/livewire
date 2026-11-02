import { on } from '@/hooks'
import { isMorphing } from '@/morph'

// Keyed elements carry a `wire:hash` of the markup the server rendered for them. Requests send
// the hashes currently in the DOM, and the server answers with an empty `wire:same` placeholder
// for any subtree that hasn't changed, which morphing then skips entirely...

// Per component: hash => the untouched server-rendered element it came from, used to rebuild a
// placeholder when morphing can't simply keep the element in place (e.g. reordered keys)...
let sources = new WeakMap

export function collectSubtreeHashes(component) {
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

    for (let parent = el; parent && ! parent.hasAttribute('wire:id'); parent = parent.parentElement) {
        parent.removeAttribute('wire:hash')
    }
}

// Packages often morph a component's markup themselves through Alpine (Filament's partials, for
// one), so report those morphs for them. This overrides Alpine's own functions for now, until
// Alpine's morph can report morphs it runs on behalf of other code itself...
export function reportMorphsOutsideLivewire(Alpine) {
    let { morph, morphBetween } = Alpine

    Alpine.morph = (from, ...args) => {
        if (isMorphing()) return morph(from, ...args)

        markChanged(from)

        let result = morph(from, ...args)

        // ...and again, in case the new markup came from an earlier render...
        markChanged(from)

        return result
    }

    Alpine.morphBetween = (startMarker, ...args) => {
        if (isMorphing()) return morphBetween(startMarker, ...args)

        let container = startMarker.parentElement

        markChanged(container)

        let result = morphBetween(startMarker, ...args)

        markChanged(container)

        return result
    }
}

on('morph', ({ toEl, component }) => beginMorph(component, toEl))

// Right after the morph, while moved elements can still be put back without Alpine
// tearing them down as removed...
on('morph.finished', ({ component }) => finishMorph(component))

// An island only brings part of the markup, so it adds to what's stored instead of replacing it...
on('island.morph', ({ toEl, component }) => beginMorph(component, toEl, true))

on('island.morphed', ({ startNode }) => {
    // ...and the keyed elements around it now hold markup the server didn't send...
    for (let el = startNode.parentElement; el && ! el.hasAttribute('wire:id'); el = el.parentElement) {
        el.removeAttribute('wire:hash')
    }
})

function beginMorph(component, toEl, partial = false) {
    let previous = sources.get(component) ?? new Map
    let next = partial ? new Map(previous) : new Map

    let retain = (hash, node, force = false) => {
        if (! node || (! force && next.has(hash))) return

        next.set(hash, node)

        // Stored markup may itself hold placeholders, so keep what those point to as well...
        keyedDescendants(node).forEach(el => {
            let nested = el.getAttribute('wire:hash')

            retain(nested, el.hasAttribute('wire:same') ? previous.get(nested) : el)
        })
    }

    let wanted = new Set

    keyedDescendants(toEl).forEach(el => {
        let hash = el.getAttribute('wire:hash')

        if (el.hasAttribute('wire:same')) wanted.add(hash)

        retain(hash, el.hasAttribute('wire:same') ? previous.get(hash) : el, partial && ! el.hasAttribute('wire:same'))
    })

    sources.set(component, next)

    // The live elements those placeholders stand for, in case morphing re-adds instead of moving...
    let originals = new Map

    keyedDescendants(component.el).forEach(el => {
        let hash = el.getAttribute('wire:hash')

        if (wanted.has(hash) && ! originals.has(hash)) originals.set(hash, el)
    })

    component.__subtreeMorph = { originals, placed: [] }
}

function finishMorph(component) {
    let state = component.__subtreeMorph

    delete component.__subtreeMorph

    state?.placed.forEach(placeholder => {
        let original = state.originals.get(placeholder.getAttribute('wire:hash'))

        if (original && original !== placeholder && ! original.contains(placeholder)) {
            placeholder.replaceWith(original)

            return
        }

        // Nothing to put there, so it must never be claimed as up to date...
        placeholder.removeAttribute('wire:hash')

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

    component.$wire.$refresh()
}

on('morph.updating', ({ el, toEl, component, skip }) => {
    if (! toEl.hasAttribute('wire:same')) {
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
    if (! el.hasAttribute) return

    let placeholders = el.hasAttribute('wire:same') ? [el] : []

    el.querySelectorAll('[wire\\:same]').forEach(i => placeholders.push(i))

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

    if (el.hasAttribute('wire:same')) state.placed.push(el)

    el.querySelectorAll('[wire\\:same]').forEach(i => state.placed.push(i))
})

function fill(placeholder, component) {
    let source = sources.get(component)?.get(placeholder.getAttribute('wire:hash'))

    if (! source) return false

    Array.from(placeholder.attributes).forEach(attr => placeholder.removeAttribute(attr.name))
    Array.from(source.attributes).forEach(attr => placeholder.setAttributeNode(attr.cloneNode()))

    placeholder.replaceChildren(...Array.from(source.childNodes, node => node.cloneNode(true)))

    return keyedDescendants(placeholder)
        .filter(el => el.hasAttribute('wire:same'))
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
