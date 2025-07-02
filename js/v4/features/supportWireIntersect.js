import Alpine from 'alpinejs'
import interceptor from '@/v4/interceptors/interceptors.js'
import { wireIslandHook } from './supportWireIsland'
import { extractDirective } from '@/directives'
import { on } from '@/hooks'

let shouldPreserveScroll = false

on('commit', ({ component, respond }) => {
    respond(() => {
        if (shouldPreserveScroll) {
            let oldHeight = document.body.scrollHeight;
            let oldScroll = window.scrollY;

            setTimeout(() => {
                let heightDiff = document.body.scrollHeight - oldHeight;
                window.scrollTo(0, oldScroll + heightDiff);

                shouldPreserveScroll = false
            })
        }
    })
})

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:intersect')) {
            let { name, value } = el.attributes[i]

            let directive = extractDirective(el, name)

            console.log('intersect',directive)

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

                    if (modifierString.includes('.preserve-scroll')) {
                        shouldPreserveScroll = true
                    }

                    evaluator()
                }
            })
        }
    }
})