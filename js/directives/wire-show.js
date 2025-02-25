import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    if (!el.hasAttribute('wire:show')) return

    let value = el.getAttribute('wire:show')

    let expression = value.startsWith('!')
        ? '!$wire.' + value.slice(1).trim()
        : '$wire.' + value.trim()

    Alpine.bind(el, {
        ['x-show']() {
            return Alpine.evaluate(el, expression)
        }
    })
})