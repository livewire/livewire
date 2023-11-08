import { on } from '@/events'
import Alpine from 'alpinejs'

let staticPartials = []

on('effects', (component, effects) => {
    let bypassedStatics = effects.bypassedStatics
    let newStatics = effects.newStatics
    let html = effects.html

    // Let's store the HTML for any new "rendered" chunks that got sent over the wire.
    // We want to store these "pre-initialization" so that we swap them in on future
    // requests and simulate what the server-side HTML would be...
    if (newStatics) {
        // Create a temporary element from pre-initialized HTML to easily find static elements...
        let container = html ? createElement(html) : component.el.cloneNode(true)

        newStatics.forEach((hash) => {
            // Grap the static partial out of the pre-initialized HTML...
            let el = deepQuerySelectorAll(container, '[wire\\:static="'+hash+'"]')[0]

            if (! el) throw new 'Cannot locate a matching static on page for key: '+hash

            // Store it in a cache for future reference...
            staticPartials[hash] = el.outerHTML
        })
    }

    // "bypassedStatics" is already in order from deeply nested out, so we can simply
    // iterate through it and non-greedily look for matches and everything should work.
    if (bypassedStatics && html) {
        let runningHtml = html

        bypassedStatics.forEach((hash) => {
            let staticContent = staticPartials[hash]

            if (! staticContent) throw new 'Cannot find cached static for: '+hash

            let regex = new RegExp(`\\[static:${hash}\\](.*?)\\[endstatic:${hash}\\]`, 's')

            // Replace a static placeholder with the cached HTML and inject slotted/dynamic content...
            runningHtml = runningHtml.replace(regex, (match, group) => {
                // Create a temporary element for slotting...
                let preSlottedHtmlEl = createElement(staticContent)

                // Find all the slots inside the static partial (that may contain template tags)...
                let slotEls = deepQuerySelectorAll(preSlottedHtmlEl, '[wire\\:dynamic="'+hash+'"]')

                // Extract all the slot placeholders for re-injection...
                regex = new RegExp(`\\[dynamic:${hash}\\](.*?)\\[enddynamic:${hash}\\]`, 'gs')

                let matches = [...group.matchAll(regex)]

                let slotContents = matches.map(match => match[1])

                // Ensure there aren't any more or less slots provided than we have a place for in the cached partial...
                if (slotContents.length !== slotEls.length) throw new 'Number of static slots doesnt match runtime slots'

                // Replace all temp slot elements with fresh slot markup ...
                slotEls.forEach((el, idx) => {
                    el.outerHTML = slotContents[idx]
                })

                return preSlottedHtmlEl.outerHTML
            })
        })

        // Override the HTML from the server for future hooks including morphdom...
        effects.html = runningHtml
    }
})

function createElement(html) {
    const template = document.createElement('template')

    template.innerHTML = html

    return template.content.firstElementChild
}

function deepQuerySelectorAll(parentNode, selector) {
    // Array to hold all matching elements
    let elements = [];

    // Immediately invoke a function to search the parent node
    (function searchNode(node) {
      // Add all matching elements at this level to the array
      elements = elements.concat(Array.from(node.querySelectorAll(selector)));

      // Find all template tags at this level
      const templates = Array.from(node.querySelectorAll('template'));

      // For each template, search its content
      templates.forEach(template => {
        searchNode(template.content);
      });
    })(parentNode);

    return elements;
  }


