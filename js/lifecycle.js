import { closestComponent, destroyComponent, initComponent } from './store'
import { matchesForLivewireDirective, extractDirective } from './directives'
import { trigger } from './hooks'
import collapse from '@alpinejs/collapse'
import focus from '@alpinejs/focus'
import persist from '@alpinejs/persist'
import intersect from '@alpinejs/intersect'
import resize from '@alpinejs/resize'
import anchor from '@alpinejs/anchor'
import navigate from './plugins/navigate'
import history from './plugins/history'
import morph from '@alpinejs/morph'
import mask from '@alpinejs/mask'
import Alpine from 'alpinejs'
import { dispatch } from './utils'

export function start() {
    setTimeout(() => ensureLivewireScriptIsntMisplaced())

    dispatch(document, 'livewire:init')
    dispatch(document, 'livewire:initializing')

    Alpine.plugin(morph)
    Alpine.plugin(history)
    Alpine.plugin(intersect)
    Alpine.plugin(resize)
    Alpine.plugin(collapse)
    Alpine.plugin(anchor)
    Alpine.plugin(focus)
    Alpine.plugin(persist)
    Alpine.plugin(navigate)
    Alpine.plugin(mask)

    Alpine.addRootSelector(() => '[wire\\:id]')

    Alpine.onAttributesAdded((el, attributes) => {
        // if there are no "wire:" directives we don't need to process this element any further.
        // This prevents Livewire from causing general slowness for other Alpine elements on the page...
        if (! Array.from(attributes).some(attribute => matchesForLivewireDirective(attribute.name))) return

        let component = closestComponent(el, false)

        if (! component) return

        attributes.forEach(attribute => {
            if (! matchesForLivewireDirective(attribute.name)) return;

            let directive = extractDirective(el, attribute.name)

            trigger('directive.init', { el, component, directive, cleanup: (callback) => {
                Alpine.onAttributeRemoved(el, directive.raw, callback)
            } })
        })
    })

    Alpine.interceptInit(
        Alpine.skipDuringClone(el => {
            // if there are no "wire:" directives we don't need to process this element any further.
            // This prevents Livewire from causing general slowness for other Alpine elements on the page...
            if (! Array.from(el.attributes).some(attribute => matchesForLivewireDirective(attribute.name))) return

            if (el.hasAttribute('wire:id')) {
                let component = initComponent(el)

                Alpine.onAttributeRemoved(el, 'wire:id', () => {
                    destroyComponent(component.id)
                })
            }

            let directives = Array.from(el.getAttributeNames())
                .filter(name => matchesForLivewireDirective(name))
                .map(name => extractDirective(el, name))

            directives.forEach(directive => {
                trigger('directive.global.init', { el, directive, cleanup: (callback) => {
                    Alpine.onAttributeRemoved(el, directive.raw, callback)
                } })
            })

            let component = closestComponent(el, false)

            if (component) {
                trigger('element.init', { el, component })

                directives.forEach(directive => {
                    trigger('directive.init', { el, component, directive, cleanup: (callback) => {
                        Alpine.onAttributeRemoved(el, directive.raw, callback)
                    } })
                })
            }
        })
    )

    Alpine.start()

    setTimeout(() => window.Livewire.initialRenderIsFinished = true)

    dispatch(document, 'livewire:initialized')
}

function ensureLivewireScriptIsntMisplaced() {
    let el = document.querySelector('script[data-update-uri][data-csrf]')

    // If there is no Livewire-injected script on the page, move on...
    if (! el) return

    // If there is, let's ensure it's at the top-level. If it's nested
    // inside a normal element, that probably means that a closing
    // tag was missing in the template and Chrome moved the tag.

    // We're only checking for "div" here because it's quick and useful...
    let livewireEl = el.closest('[wire\\:id]')

    if (livewireEl) {
        console.warn('Livewire: missing closing tags found. Ensure your template elements contain matching closing tags.', livewireEl)
    }
}
