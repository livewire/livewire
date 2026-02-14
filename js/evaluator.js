import Alpine from 'alpinejs'

function parseForExpression(expression) {
    let forIteratorRE = /,([^,\}\]]*)(?:,([^,\}\]]*))?$/
    let stripParensRE = /^\s*\(|\)\s*$/g
    let forAliasRE = /([\s\S]*?)\s+(?:in|of)\s+([\s\S]*)/
    let inMatch = expression.match(forAliasRE)
    if (!inMatch) return null

    let res = {}
    res.items = inMatch[2].trim()
    let item = inMatch[1].replace(stripParensRE, '').trim()
    let iteratorMatch = item.match(forIteratorRE)

    if (iteratorMatch) {
        res.item = item.replace(forIteratorRE, '').trim()
        res.index = iteratorMatch[1].trim()
        if (iteratorMatch[2]) {
            res.collection = iteratorMatch[2].trim()
        }
    } else {
        res.item = item
    }
    return res
}

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

    let contextualExpression = contextualizeExpression(expression, el)

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

export function contextualizeExpression(expression, el) {
    let SKIP = ['JSON', 'true', 'false', 'null', 'undefined', 'this', '$wire', '$event']

    // If an element is provided, collect x-for loop variables (item, index, collection)
    // so they don't get incorrectly prefixed with $wire.
    //
    // Key insight: We ONLY skip loop variables from x-for, not all Alpine scope.
    // This allows Livewire properties to intentionally shadow Alpine x-data variables.
    //
    // Example: If Alpine has x-data="{ user: 'alpine' }" and Livewire has $user,
    // expressions like wire:click="doSomething(user)" should reference $wire.user,
    // not the Alpine variable. Only loop variables like x-for="user in users"
    // need to be skipped.
    //
    // Performance: We walk the DOM tree once per expression evaluation, only checking
    // for x-for attributes. This is O(depth) where depth is typically small (< 10).
    if (el) {
        let currentEl = el
        while (currentEl && currentEl.nodeType === 1) {
            let xForAttr = currentEl.getAttribute('x-for')
            if (xForAttr) {
                let loopVars = parseForExpression(xForAttr)
                if (loopVars) {
                    if (loopVars.item && !SKIP.includes(loopVars.item)) SKIP.push(loopVars.item)
                    if (loopVars.index && !SKIP.includes(loopVars.index)) SKIP.push(loopVars.index)
                    if (loopVars.collection && !SKIP.includes(loopVars.collection)) SKIP.push(loopVars.collection)
                }
            }
            currentEl = currentEl.parentElement
        }
    }
    let strings = []

    // 1. Yank out string literals so we don't touch them
    let result = expression.replace(/(["'`])(?:(?!\1)[^\\]|\\.)*\1/g, (m) => {
        strings.push(m)
        return `___${strings.length - 1}___`
    })

    // 2. Prefix identifiers not after a dot (skip placeholders from step 1)
    //    Also skip object keys (identifiers immediately followed by colon)
    result = result.replace(/(^|[^.\w$])(\$?[a-zA-Z_]\w*)/g, (m, pre, ident, offset) => {
        if (SKIP.includes(ident) || /^___\d+___$/.test(ident)) return pre + ident
        if (result[offset + m.length] === ':') return pre + ident
        return pre + '$wire.' + ident
    })

    // 3. Restore strings
    return result.replace(/___(\d+)___/g, (m, i) => strings[i])
}
