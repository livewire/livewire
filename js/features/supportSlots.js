import { on } from '@/hooks'

export function skipSlotContents(el, toEl, skipUntil) {
    if (isStartMarker(el) && isStartMarker(toEl)) {
        skipUntil(node => isEndMarker(node))
    }
}

function isStartMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if SLOT:')
}

function isEndMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ENDSLOT]')
}