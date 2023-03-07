import { monkeyPatchDomSetAttributeToAllowAtSymbols } from 'utils'
import { closestComponent, initComponent } from './store'
import { initDirectives } from './directives'
import { trigger } from './events'
import intersect from '@alpinejs/intersect'
import history from '@alpinejs/history'
import morph from '@alpinejs/morph'
import Alpine from 'alpinejs'

export function start() {
    monkeyPatchDomSetAttributeToAllowAtSymbols()

    Alpine.plugin(morph)
    Alpine.plugin(history)
    Alpine.plugin(intersect)

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


