import Alpine from 'alpinejs'

function getAlpineScopeKeys(el) {
    let keys = []
    let currentEl = el

    while (currentEl) {
        if (currentEl._x_dataStack && currentEl._x_dataStack.length > 0) {
            // Only read the first scope object -- this is the element's OWN
            // data, not inherited parent scopes. Alpine's addScopeToNode()
            // always puts the element's own data at index 0, followed by
            // the parent chain. Reading all entries would leak parent Alpine
            // scope keys across the Livewire component boundary.
            let ownScope = currentEl._x_dataStack[0]

            for (let key of Object.keys(ownScope)) {
                if (! keys.includes(key) && ! key.startsWith('$')) keys.push(key)
            }
        }

        // Stop at the Livewire component root element...
        if (currentEl.hasAttribute && currentEl.hasAttribute('wire:id')) break

        currentEl = currentEl.parentElement
    }

    return keys
}

export function evaluateExpression(el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    // Bad expressions here arrive from the server as effects (`$this->js()`),
    // so they're evaluated mid-response. Letting one throw would tear down
    // the rest of the response handling, so report it and move on...
    try {
        let result = Alpine.evaluateRaw(el, expression, options)

        if (result instanceof Promise) {
            result.catch(() => {})
        }

        return result
    } catch (error) {
        reportExpressionError(error, expression, el)
    }
}

let reactiveExpressionEvaluationDepth = 0

export function isEvaluatingReactiveExpression() {
    return reactiveExpressionEvaluationDepth > 0
}

export function evaluateReactiveExpression(el, expression, options = {}) {
    reactiveExpressionEvaluationDepth++

    try {
        return evaluateActionExpression(el, expression, options)
    } finally {
        reactiveExpressionEvaluationDepth--
    }
}

export function evaluateActionExpression(el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    let contextualExpression = contextualizeExpression(expression, el, ! isEvaluatingReactiveExpression())

    try {
        let result = Alpine.evaluateRaw(el, contextualExpression, options)

        // Silently catch Livewire request failures. These are handled by
        // Livewire at the request level...
        if (result instanceof Promise && result._livewireAction) {
            result.catch(() => {})
        }

        return result
    } catch (error) {
        reportExpressionError(error, expression, el)
    }
}

function reportExpressionError(error, expression, el) {
    console.warn(`Livewire Expression Error: ${error.message}\n\n${ expression ? 'Expression: \"' + expression + '\"\n\n' : '' }`, el)

    console.error(error)
}

export function contextualizeExpression(expression, el, preferWireAction = false) {
    let SKIP = ['JSON', 'true', 'false', 'null', 'undefined', 'this', '$wire', '$event']
    let alpineScopeKeys = []

    // If an element is provided, collect Alpine scope keys between
    // this element and the Livewire component root so they don't
    // get incorrectly prefixed with $wire.
    if (el) {
        alpineScopeKeys = getAlpineScopeKeys(el)
        SKIP.push(...alpineScopeKeys)
    }
    let protectedExpressions = []

    let actionTargetOffset = preferWireAction
        ? getActionTargetOffset(expression)
        : null

    // 1. Yank out string literals and comments so we don't touch them
    let result = expression.replace(/(["'`])(?:(?!\1)[^\\]|\\.)*\1|\/\*[\s\S]*?\*\/|\/\/[^\n]*/g, (m) => {
        protectedExpressions.push(m)
        return `___${protectedExpressions.length - 1}___`
    })

    // 2. Prefix identifiers not after a dot (skip placeholders from step 1)
    //    Also skip object keys (identifiers immediately followed by colon)
    result = result.replace(/(^|[^.\w$])(\$?[a-zA-Z_]\w*)/g, (m, pre, ident, offset) => {
        let isWireActionTarget = alpineScopeKeys.includes(ident)
            && offset + pre.length === actionTargetOffset

        if ((SKIP.includes(ident) && ! isWireActionTarget) || /^___\d+___$/.test(ident)) return pre + ident
        if (result[offset + m.length] === ':') return pre + ident
        return pre + '$wire.' + ident
    })

    // 3. Restore strings and comments
    return result.replace(/___(\d+)___/g, (m, i) => protectedExpressions[i])
}

function getActionTargetOffset(expression) {
    let actionTarget = expression.match(/^(\s*)([a-zA-Z_]\w*)/)

    if (! actionTarget) return null

    let remainder = expression.slice(actionTarget[0].length)
    let significantRemainder = remainder.replace(/^(?:(?:\s+)|(?:\/\*[\s\S]*?\*\/)|(?:\/\/[^\n]*(?:\n|$)))*/, '')

    if (significantRemainder !== '' && ! significantRemainder.startsWith('(') && ! significantRemainder.startsWith(';')) return null

    return actionTarget[1].length
}
