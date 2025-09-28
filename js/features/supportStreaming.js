import { findRefEl } from '@/features/supportRefs'
import { findComponent } from '@/store'
import { on } from '@/hooks'

on('stream', (payload) => {
    let { id, name, el, ref, content, mode } = payload

    let component = findComponent(id)

    let targetEl = null

    if (name) {
        replaceEl = component.el.querySelector(`[wire\\:stream.replace="${name}"]`)

        if (replaceEl) {
            targetEl = replaceEl
            mode = 'replace'
        } else {
            targetEl = component.el.querySelector(`[wire\\:stream="${name}"]`)
        }
    } else if (ref) {
        targetEl = findRefEl(component, ref)
    } else if (el) {
        targetEl = component.el.querySelector(el)
    }

    if (! targetEl) return // Noop...

    if (mode === 'replace') {
        targetEl.innerHTML = content
    } else {
        targetEl.insertAdjacentHTML('beforeend', content)
    }
})
