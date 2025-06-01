import { morph } from '@/morph'
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
                let outerHTML = component.el.outerHTML

                let start = `<!--[if PARTIAL:${name}]><![endif]-->`
                let end = `<!--[if ENDPARTIAL:${name}]><![endif]-->`
                let startIndex = outerHTML.indexOf(start)
                let endIndex = outerHTML.indexOf(end) + end.length

                // Strip the markers from the incoming content
                let strippedContent = content
                    .replace(new RegExp(`<!--\\[if PARTIAL:${name}]><\\!\\[endif]-->`, 'g'), '')
                    .replace(new RegExp(`<!--\\[if ENDPARTIAL:${name}]><\\!\\[endif]-->`, 'g'), '')

                if (mode === 'prepend') {
                    // Add content after start marker
                    outerHTML = outerHTML.slice(0, startIndex + start.length) +
                               strippedContent +
                               outerHTML.slice(startIndex + start.length)
                } else if (mode === 'append') {
                    // Add content before end marker
                    outerHTML = outerHTML.slice(0, endIndex - end.length) +
                               strippedContent +
                               outerHTML.slice(endIndex - end.length)
                } else {
                    // Default replace behavior
                    outerHTML = outerHTML.slice(0, startIndex) + content + outerHTML.slice(endIndex)
                }

                morph(component, component.el, outerHTML)
            })
        })
    })
})
