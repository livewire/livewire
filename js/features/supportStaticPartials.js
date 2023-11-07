import { on } from '@/events'
import Alpine from 'alpinejs'

let staticCache = []

on('effects', (component, effects) => {
    let renderedStatics = effects.renderedStatics
    let newStatics = effects.newStatics
    let html = effects.html

    // Let's store the HTML for any new "rendered" chunks that got sent over the wire.
    // We want to store these "pre-initialization" so that we swap them in on future
    // requests and simulate what the server-side HTML would be...
    if (newStatics) {
        let container = html ? createElement(html) : component.el.cloneNode(true)

        newStatics.forEach((key) => {
            let eligableStaticEls = container.querySelectorAll('[wire\\:static="'+key+'"]')

            let el

            for (let i of eligableStaticEls) {
                if (i.__lw_alreadyUsed) continue

                el = i

                el.__lw_alreadyUsed = true

                break;
            }

            if (! el) throw new 'Cannot locate a matching static on page for key: '+key

            staticCache[key] = el.outerHTML
        })
    }

    // "renderedStatics" is already in order from deeply nested out, so we can simply
    // iterate through it and non-greedily look for matches and everything should work.
    if (renderedStatics && html) {
        let runningHtml = html

        renderedStatics.forEach((key) => {
            let staticContent = staticCache[key]
            if (! staticContent) throw new 'Cannot find cached static for: '+key

            let regex = new RegExp(`\\[STATICSTART:${key}\\](.*?)\\[STATICEND:${key}\\]`, 's')

            runningHtml = runningHtml.replace(regex, (match, group) => {
                let preSlottedHtmlEl = createElement(staticContent)
                let slotEls = preSlottedHtmlEl.querySelectorAll('[wire\\:static-slot="'+key+'"]')
                regex = new RegExp(`\\[STATICSLOTSTART:${key}\\](.*?)\\[STATICSLOTEND:${key}\\]`, 'gs')
                let matches = [...group.matchAll(regex)]
                let slotContents = matches.map(match => match[1])

                if (slotContents.length !== slotEls.length) throw new 'Number of static slots doesnt match runtime slots'

                slotEls.forEach((el, idx) => {
                    el.outerHTML = slotContents[idx]
                })

                return preSlottedHtmlEl.outerHTML
            })
        })

        effects.html = runningHtml
    }
})

function createElement(html) {
    const template = document.createElement('template')
    template.innerHTML = html
    return template.content.firstElementChild
}
