import store from '@/Store'

export default function () {
    store.registerHook('message.received', (message, component) => {
        let response = message.response

        if (! response.effects.forStack) return

        // Let's store the updates in an array for execution after the loop,
        // this way we can avoid keyHasAlreadyBeenAddedToTheStack races.
        let updates = []
        
        response.effects.forStack.forEach(({ key, stack, type, contents }) => {
            let startEl = document.querySelector(`[livewire-stack="${stack}"]`)
            let endEl = document.querySelector(`[livewire-end-stack="${stack}"]`)
            if (! startEl || ! endEl) return

            if (keyHasAlreadyBeenAddedToTheStack(startEl, endEl, key)) return

            let prepend = el => startEl.parentElement.insertBefore(el, startEl.nextElementSibling)
            let push = el => endEl.parentElement.insertBefore(el, endEl)

            let frag = createFragment(contents)

            updates.push(() => type === 'push' ? push(frag) : prepend(frag))
        })

        while (updates.length > 0) updates.shift()() 
    })
}

function keyHasAlreadyBeenAddedToTheStack(startEl, endEl, key) {
    let findKeyMarker = el => {
        if (el.isSameNode(endEl)) return

        return el.matches(`[livewire-stack-key="${key}"]`) ? el : findKeyMarker(el.nextElementSibling)
    }

    return findKeyMarker(startEl)
}

function createFragment(html) {
    return document.createRange().createContextualFragment(html)
}
