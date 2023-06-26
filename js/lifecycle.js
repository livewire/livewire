import { closestComponent, initComponent } from './store'
import { initDirectives } from './directives'
import { trigger } from './events'
import collapse from '@alpinejs/collapse'
import focus from '@alpinejs/focus'
import persist from '@alpinejs/persist'
import intersect from '@alpinejs/intersect'
import navigate from '@alpinejs/navigate'
import history from '@alpinejs/history'
import morph from '@alpinejs/morph'
import Alpine from 'alpinejs'

export function start() {
    Alpine.plugin(morph)
    Alpine.plugin(history)
    Alpine.plugin(intersect)
    Alpine.plugin(collapse)
    Alpine.plugin(focus)
    Alpine.plugin(persist)
    Alpine.plugin(navigate)

    Alpine.addRootSelector(() => '[wire\\:id]')

    Alpine.interceptInit(
        Alpine.skipDuringClone(el => {
            if (el.hasAttribute('wire:id')) initComponent(el)

            let component = closestComponent(el, false)

            if (component) {
                initDirectives(el, component)

                trigger('element.init', el, component)
            }
        })
    )

    Alpine.start()

    setTimeout(() => window.Livewire.initialRenderIsFinished = true)
}


