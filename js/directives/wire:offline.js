import { toggleBooleanStateDirective } from './shared'
import { directive } from '@/directives'

let offlineHandlers = new Set
let onlineHandlers = new Set

window.addEventListener('offline', () => offlineHandlers.forEach(i => i()))
window.addEventListener('online', () => onlineHandlers.forEach(i => i()))

directive('offline', ({ el, directive, cleanup }) => {
    let setOffline = () => toggleBooleanStateDirective(el, directive, true)
    let setOnline = () => toggleBooleanStateDirective(el, directive, false)

    offlineHandlers.add(setOffline)
    onlineHandlers.add(setOnline)

    cleanup(() => {
        offlineHandlers.delete(setOffline)
        onlineHandlers.delete(setOnline)
    })
})
