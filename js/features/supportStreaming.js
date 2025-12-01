import { findRefEl } from '@/features/supportRefs'
import { interceptMessage } from '@/request'
import { findComponent } from '@/store'

interceptMessage(({ message, onStream }) => {
    onStream(({ streamedJson }) => {
        let { id, type, name, el, ref, content, mode } = streamedJson

        // Early return for islands because they are handled by the islands feature...
        if (type === 'island') return

        let component = findComponent(id)

        let targetEl = null

        if (type === 'directive') {
            replaceEl = component.el.querySelector(`[wire\\:stream\\.replace="${name}"]`)

            if (replaceEl) {
                targetEl = replaceEl
                mode = 'replace'
            } else {
                targetEl = component.el.querySelector(`[wire\\:stream="${name}"]`)
            }
        } else if (type === 'ref') {
            targetEl = findRefEl(component, ref)
        } else if (type === 'element') {
            targetEl = component.el.querySelector(el)
        }

        if (! targetEl) return // Noop...

        if (mode === 'replace') {
            targetEl.innerHTML = content
        } else {
            targetEl.insertAdjacentHTML('beforeend', content)
        }
    })
})
