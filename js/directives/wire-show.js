import Alpine from 'alpinejs'

Alpine.interceptInit(el => {
    for (let i = 0; i < el.attributes.length; i++) {
        if (el.attributes[i].name.startsWith('wire:show')) {
            let { name, value } = el.attributes[i]

            let modifierString = name.split('wire:show')[1]

            let expression = value.startsWith('!')
                ? '!$wire.' + value.slice(1).trim()
                : '$wire.' + value.trim()

            Alpine.bind(el, {
                ['x-show' + modifierString]() {
                    return Alpine.evaluate(el, expression)
                }
            })
        }
    }
})