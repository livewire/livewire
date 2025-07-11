import Alpine from 'alpinejs'
import interceptor from '@/v4/interceptors/interceptors.js'
import { extractDirective } from '@/directives'

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:intersect')) {
            let { name, value } = el.attributes[i]

            let directive = extractDirective(el, name)

            let modifierString = name.split('wire:intersect')[1]

            let expression = value.startsWith('!')
                ? '!$wire.' + value.slice(1).trim()
                : '$wire.' + value.trim()

            let evaluator = Alpine.evaluateLater(el, expression)

            Alpine.bind(el, {
                ['x-intersect' + modifierString]() {
                    // @todo: review if there is a better way to get the component...
                    let component = el.closest('[wire\\:id]')?.__livewire

                    interceptor.fire(el, directive, component)

                    evaluator()
                }
            })
        }
    }
})