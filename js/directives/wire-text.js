import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:text')) {
            let { name, value } = el.attributes[i]

            let modifierString = name.split('wire:text')[1]

            let expression = value.startsWith('!')
                ? '!$wire.' + value.slice(1).trim()
                : '$wire.' + value.trim()

            Alpine.bind(el, {
                ['x-text' + modifierString]() {
                    return Alpine.evaluate(el, expression)
                }
            })
        }
    }
})
