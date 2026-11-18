import { directive } from "@/directives"

directive('confirm', ({ el, directive, cleanup }) => {
    let message = directive.expression
    let shouldPrompt = directive.modifiers.includes('prompt')

    // Convert sanitized linebreaks ("\n") to real line breaks...
    message = message.replaceAll('\\n', '\n')

    if (message === '') message = 'Are you sure?'

    let confirmAction = (action, instead) => {
        if (shouldPrompt) {
            let [question, expected] = message.split('|')

            if (! expected) {
                console.warn('Livewire: Must provide expectation with wire:confirm.prompt')
            } else {
                let input = prompt(question)

                if (input === expected) {
                    action()
                } else {
                    instead()
                }
            }
        } else {
            if (confirm(message)) action()
            else instead()
        }
    }

    el.__livewire_confirm = confirmAction

    cleanup(() => {
        if (el.__livewire_confirm === confirmAction) delete el.__livewire_confirm
    })
})
