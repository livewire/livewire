import { on } from '@/hooks'
import { getUriPrefix } from '@/utils'

let loadedStyles = new Set()

on('effect', ({ component, effects }) => {
    // Handle scoped styles
    if (effects.styleModule) {
        let encodedName = component.name.replace('.', '--').replace('::', '---').replace(':', '----')
        let path = `${getUriPrefix()}/css/${encodedName}.css?v=${effects.styleModule}`

        if (!loadedStyles.has(path)) {
            loadedStyles.add(path)
            injectStylesheet(path)
        }
    }

    // Handle global styles
    if (effects.globalStyleModule) {
        let encodedName = component.name.replace('.', '--').replace('::', '---').replace(':', '----')
        let path = `${getUriPrefix()}/css/${encodedName}.global.css?v=${effects.globalStyleModule}`

        if (!loadedStyles.has(path)) {
            loadedStyles.add(path)
            injectStylesheet(path)
        }
    }
})

function injectStylesheet(href) {
    let link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = href
    document.head.appendChild(link)
}
