import DOM from '@/dom/dom'
import '@/dom/polyfills/index'
import store from '@/Store'
import Connection from '@/connection'
import Polling from '@/component/Polling'
import Component from '@/component/index'
import { dispatch, wireDirectives } from '@/util'
import FileUploads from '@/component/FileUploads'
import LaravelEcho from '@/component/LaravelEcho'
import DirtyStates from '@/component/DirtyStates'
import DisableForms from '@/component/DisableForms'
import FileDownloads from '@/component/FileDownloads'
import LoadingStates from '@/component/LoadingStates'
import OfflineStates from '@/component/OfflineStates'
import SyncBrowserHistory from '@/component/SyncBrowserHistory'
import SupportAlpine from '@/component/SupportAlpine'

class Livewire {
    constructor() {
        this.connection = new Connection()
        this.components = store
        this.devToolsEnabled = false
        this.onLoadCallback = () => { }
    }

    first() {
        return Object.values(this.components.componentsById)[0].$wire
    }

    find(componentId) {
        return this.components.componentsById[componentId].$wire
    }

    all() {
        return Object.values(this.components.componentsById).map(
            component => component.$wire
        )
    }

    directive(name, callback) {
        this.components.registerDirective(name, callback)
    }

    hook(name, callback) {
        this.components.registerHook(name, callback)
    }

    onLoad(callback) {
        this.onLoadCallback = callback
    }

    onError(callback) {
        this.components.onErrorCallback = callback
    }

    emit(event, ...params) {
        this.components.emit(event, ...params)
    }

    emitTo(name, event, ...params) {
        this.components.emitTo(name, event, ...params)
    }

    on(event, callback) {
        this.components.on(event, callback)
    }

    devTools(enableDevtools) {
        this.devToolsEnabled = enableDevtools
    }

    restart() {
        this.stop()
        this.start()
    }

    stop() {
        this.components.tearDownComponents()
    }

    start() {
        DOM.rootComponentElementsWithNoParents().forEach(el => {
            this.components.addComponent(new Component(el, this.connection))
        })

        this.onLoadCallback()
        dispatch('livewire:load')

        document.addEventListener(
            'visibilitychange',
            () => {
                this.components.livewireIsInBackground = document.hidden
            },
            false
        )

        this.components.initialRenderIsFinished = true
    }

    rescan(node = null) {
        DOM.rootComponentElementsWithNoParents(node).forEach(el => {
            const componentId = wireDirectives(el).get('id').value

            if (this.components.hasComponent(componentId)) return

            this.components.addComponent(new Component(el, this.connection))
        })
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

monkeyPatchDomSetAttributeToAllowAtSymbols()

SyncBrowserHistory()
SupportAlpine()
FileDownloads()
OfflineStates()
LoadingStates()
DisableForms()
FileUploads()
LaravelEcho()
DirtyStates()
Polling()

dispatch('livewire:available')

export default Livewire

function monkeyPatchDomSetAttributeToAllowAtSymbols() {
    // Because morphdom may add attributes to elements containing "@" symbols
    // like in the case of an Alpine `@click` directive, we have to patch
    // the standard Element.setAttribute method to allow this to work.
    let original = Element.prototype.setAttribute

    let hostDiv = document.createElement('div')

    Element.prototype.setAttribute = function newSetAttribute(name, value) {
        if (! name.includes('@')) {
            return original.call(this, name, value)
        }

        hostDiv.innerHTML = `<span ${name}="${value}"></span>`

        let attr = hostDiv.firstElementChild.getAttributeNode(name)

        hostDiv.firstElementChild.removeAttributeNode(attr)

        this.setAttributeNode(attr)
    }
}
