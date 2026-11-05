import { findRefEl } from '@/features/supportRefs'
import { interceptMessage } from '@/request'
import { findComponent } from '@/store'
import { markChanged } from '@/features/supportSubtreeHashes'

interceptMessage(({ message, onStream }) => {
    onStream(({ json }) => {
        let { id, type, name, el, ref, content, mode } = json

        // Early return for islands because they are handled by the islands feature...
        if (type === 'island') return

        let component = findComponent(id)

        let targetEl = null

        if (type === 'directive') {
            const replaceEl = component.el.querySelector(`[wire\\:stream\\.replace="${name}"]`)

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

        // The streamed content isn't what the server last rendered there, so nothing around it can
        // be claimed as unchanged — including by the response to the very request streaming it...
        markChanged(targetEl)

        if (mode === 'replace') {
            targetEl.innerHTML = content
        } else {
            targetEl.insertAdjacentHTML('beforeend', content)
        }
    })
})
