import { directive } from "@/directives"

directive('ignore', ({ el, directive }) => {
    if (directive.hasModifier('self')) {
        el.__livewire_ignore_self = true
    } else if (directive.hasModifier('children')) {
        el.__livewire_ignore_children = true
    } else {
        el.__livewire_ignore = true
    }
})
