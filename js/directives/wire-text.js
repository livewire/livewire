import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    if (!el.hasAttribute('wire:text')) return

    let value = el.getAttribute('wire:text')

    let expression = value.startsWith('!')
        ? '!$wire.' + value.slice(1).trim()
        : '$wire.' + value.trim()

    Alpine.bind(el, {
        ['x-text']() {
            return Alpine.evaluate(el, expression)
        }
    })
})