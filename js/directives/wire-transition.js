import { globalDirective } from "@/directives"

globalDirective('transition', ({ el, directive, cleanup }) => {
    let transitionName = directive.expression || 'match-element'

    el.style.viewTransitionName = transitionName
})

export async function transitionDomMutation(component, callback) {
    // Only transition if there is a [wire:transition] element within the component...
    if (! component.el.querySelector('[wire:transition]')) return callback()

    // Disable root transitions for the page...
    let style = document.createElement('style')

    style.textContent = `
        @media (prefers-reduced-motion: reduce) {
            ::view-transition-group(*), ::view-transition-old(*), ::view-transition-new(*) {
                animation: none !important;
            }
        }

        ::view-transition-old(root) {
            animation: none !important;
            opacity: 0 !important;
        }

        ::view-transition-new(root) {
            animation: none !important;
            opacity: 1 !important;
        }
    `

    document.head.appendChild(style)

    let transition = document.startViewTransition(() => {
        callback()
    })

    transition.finished.finally(() => {
        style.remove()
    })

    await transition.updateCallbackDone
}