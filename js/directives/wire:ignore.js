import { directive } from "@/directives"

directive('ignore', ({ el, directive }) => {
    if (directive.modifiers.includes('self')) {
        el.__livewire_ignore_self = true
    } else {
        el.__livewire_ignore = true
    }
})
