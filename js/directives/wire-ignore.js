import { directive } from "@/directives"

directive('ignore', ({ el, directive }) => {
    if (directive.modifiers.includes('self')) {
        el.__livewire_ignore_self = true
    } else if (directive.modifiers.includes('children')) {
        el.__livewire_ignore_children = true
    } else {
        el.__livewire_ignore = true
    }
})
