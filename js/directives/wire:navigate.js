import { directive } from "@/directives"
import Alpine from 'alpinejs'

Alpine.addInitSelector(() => `[wire\\:navigate]`)
Alpine.addInitSelector(() => `[wire\\:navigate\\.hover]`)

Alpine.interceptInit(
    Alpine.skipDuringClone(el => {
        if (el.hasAttribute('wire:navigate')) {
            Alpine.bind(el, { ['x-navigate']: true })
        } else if (el.hasAttribute('wire:navigate.hover')) {
            Alpine.bind(el, { ['x-navigate.hover']: true })
        }
    })
)
