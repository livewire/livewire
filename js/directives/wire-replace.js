import { directive } from "@/directives"

directive('replace', ({ el, directive }) => {
    if (directive.modifiers.includes('self')) {
        el.__livewire_replace_self = true
    } else {
        el.__livewire_replace = true
    }
})
