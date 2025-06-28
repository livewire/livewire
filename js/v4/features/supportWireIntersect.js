import Alpine from 'alpinejs'
import { wireIslandHook } from './supportWireIsland'
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

            let modifierString = name.split('wire:intersect')[1]

            let expression = value.startsWith('!')
                ? '!$wire.' + value.slice(1).trim()
                : '$wire.' + value.trim()

            let evaluator = Alpine.evaluateLater(el, expression)

            Alpine.bind(el, {
                ['x-intersect' + modifierString]() {
                    // @todo: this is a V4 hack to get wire:island working...
                    wireIslandHook(el)

                    if (modifierString.includes('.preserve-scroll')) {
                        shouldPreserveScroll = true
                    }

                    evaluator()
                }
            })
        }
    }
})