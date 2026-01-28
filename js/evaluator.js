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

    let contextualExpression = contextualizeExpression(expression)

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

export function contextualizeExpression(expression) {
    let SKIP = ['JSON', 'true', 'false', 'null', 'undefined', 'this', '$wire', '$event']
    let strings = []

    // 1. Yank out string literals so we don't touch them
    let result = expression.replace(/(["'`])(?:(?!\1)[^\\]|\\.)*\1/g, (m) => {
        strings.push(m)
        return `___${strings.length - 1}___`
    })

    // 2. Prefix identifiers not after a dot (skip placeholders from step 1)
    //    Also skip object keys (identifiers immediately followed by colon)
    result = result.replace(/(?<![.\w$])(\$?[a-zA-Z_]\w*)/g, (m, ident, offset) => {
        if (SKIP.includes(ident) || /^___\d+___$/.test(ident)) return ident
        if (result[offset + m.length] === ':') return ident
        return '$wire.' + ident
    })

    // 3. Restore strings
    return result.replace(/___(\d+)___/g, (m, i) => strings[i])
}
