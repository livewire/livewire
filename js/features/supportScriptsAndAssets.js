import { on } from '@/events'
import Alpine from 'alpinejs'

on('effects', (component, effects) => {
    let assets = effects.assets

    if (assets) {
        Object.entries(assets).forEach(([key, content]) => {
            onlyIfAssetsHaventBeenLoadedAlreadyOnThisPage(key, () => {
                addAssetsToHeadTagOfPage(content)
            })
        })
    }
})

on('effects', (component, effects) => {
    let scripts = effects.scripts

    if (scripts) {
        Object.entries(scripts).forEach(([key, content]) => {
            onlyIfScriptHasntBeenRunAlreadyForThisComponent(component, key, () => {
                let scriptContent = extractScriptTagContent(content)

                Alpine.evaluate(component.el, scriptContent, { '$wire': component.$wire })
            })
        })
    }
})

let executedScripts = new WeakMap

function onlyIfScriptHasntBeenRunAlreadyForThisComponent(component, key, callback) {
    if (executedScripts.has(component)) {
        let alreadyRunKeys = executedScripts.get(component)

        if (alreadyRunKeys.includes(key)) return
    }

    callback()

    if (! executedScripts.has(component)) executedScripts.set(component, [])

    let alreadyRunKeys = executedScripts.get(component)

    alreadyRunKeys.push(key)

    executedScripts.set(component, alreadyRunKeys)
}

function extractScriptTagContent(rawHtml) {
    let scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gm
    let matches = scriptRegex.exec(rawHtml)
    let innards =  matches && matches[1] ? matches[1].trim() : ''

    return innards
}

let executedAssets = new Set

function onlyIfAssetsHaventBeenLoadedAlreadyOnThisPage(key, callback) {
    if (executedAssets.has(key)) return

    callback()

    executedAssets.add(key)
}

function addAssetsToHeadTagOfPage(rawHtml) {
    let newDocument = (new DOMParser()).parseFromString(rawHtml, "text/html")
    let newHead = document.adoptNode(newDocument.head)

    for (let child of newHead.children) {
        if (isScript(child)) {
            document.head.appendChild(cloneScriptTag(child))
        } else {
            document.head.appendChild(child)
        }
    }
}

function isScript(el)   {
    return el.tagName.toLowerCase() === 'script'
}

function cloneScriptTag(el) {
    let script = document.createElement('script')

    script.textContent = el.textContent
    script.async = el.async

    for (let attr of el.attributes) {
        script.setAttribute(attr.name, attr.value)
    }

    return script
}
