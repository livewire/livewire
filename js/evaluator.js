import Alpine from 'alpinejs'

export function evaluateExpression(component, el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    options = {
        ...{
            scope: {
                $wire: component.$wire,
            },
            context: component.$wire,
            ...options.scope,
            ...options.context,
        },
        ...options,
    }

    return Alpine.evaluate(el, expression, options)
}

export function evaluateActionExpression(component, el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    let negated = false

    if (expression.startsWith('!')) {
        negated = true

        expression = expression.slice(1).trim()
    }

    let contextualExpression = negated ? `! $wire.${expression}` : `$wire.${expression}`

    return Alpine.evaluate(el, contextualExpression, options)
}

export function evaluateActionExpressionWithoutComponentScope(el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    let negated = false

    if (expression.startsWith('!')) {
        negated = true

        expression = expression.slice(1).trim()
    }

    let contextualExpression = negated ? `! $wire.${expression}` : `$wire.${expression}`

    return Alpine.evaluate(el, contextualExpression, options)
}
