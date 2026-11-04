import { getDirectives } from '@/directives'

// Livewire pages are sent with "Cache-Control: no-store", so going back to one
// reloads it instead of taking it from the back/forward cache. Once the page is
// rendered again, the browser puts back whatever the user had typed into its form
// fields, and it does that without firing any "input" or "change" events. wire:model
// only learns about a new value through those events, so the field shows one value
// while the component still holds the one it rendered with...

// We remember what every wire:model field shows right after Livewire boots, before
// the browser restores anything. By the first "pageshow" the browser is done, so we
// replay the event a user's edit would fire on each field it changed, and wire:model
// picks up the restored value like it had been typed.
document.addEventListener('livewire:initialized', () => {
    let fields = Array.from(document.querySelectorAll('input, textarea, select'))
        .filter(el => getDirectives(el).has('model'))

    let initialValues = new Map(fields.map(el => [el, valueOf(el)]))

    window.addEventListener('pageshow', (event) => {
        // A page from the back/forward cache kept its component state along with
        // its fields, so there is nothing to catch up on...
        if (event.persisted) return

        initialValues.forEach((value, el) => {
            if (valueOf(el) === value) return

            el.dispatchEvent(new Event('input', { bubbles: true }))
            el.dispatchEvent(new Event('change', { bubbles: true }))
        })
    }, { once: true })
}, { once: true })

function valueOf(el) {
    if (el.type === 'checkbox' || el.type === 'radio') return el.checked

    if (el.tagName === 'SELECT' && el.multiple) {
        return Array.from(el.selectedOptions).map(option => option.value).join(',')
    }

    return el.value
}
