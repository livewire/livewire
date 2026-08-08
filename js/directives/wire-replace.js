import { directive } from "@/directives"

directive('replace', ({ el, directive }) => {
    if (directive.hasModifier('self')) {
        el.__livewire_replace_self = true
    } else {
        el.__livewire_replace = true
    }
})
