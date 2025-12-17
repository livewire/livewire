import Alpine from 'alpinejs'

export function evaluateExpression(el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    let result = Alpine.evaluateRaw(el, expression, options)

    if (result instanceof Promise) {
        result.catch(() => {})
    }

    return result
}

export function evaluateActionExpression(el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    let negated = false

    if (expression.startsWith('!')) {
        negated = true

        expression = expression.slice(1).trim()
    }

    let contextualExpression = negated ? `! $wire.${expression}` : `$wire.${expression}`

    try {
        let result = Alpine.evaluateRaw(el, contextualExpression, options)

        // Silently catch Livewire request failures. These are handled by
        // Livewire at the request level...
        if (result instanceof Promise && result._livewireAction) {
            result.catch(() => {})
        }

        return result
    } catch (error) {
        console.warn(`Livewire Expression Error: ${error.message}\n\n${ expression ? 'Expression: \"' + expression + '\"\n\n' : '' }`, el)

        console.error(error)
    }
}
