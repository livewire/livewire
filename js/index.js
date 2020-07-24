import '@/dom/polyfills/index'
import componentStore from '@/Store'
import DOM from '@/dom/dom'
import Component from '@/component/index'
import Connection from '@/connection'
import { dispatch } from './util'
import FileUploads from '@/component/FileUploads'
import FileDownloads from '@/component/FileDownloads'
import LoadingStates from '@/component/LoadingStates'
import DisableForms from '@/component/DisableForms'
import DirtyStates from '@/component/DirtyStates'
import OfflineStates from '@/component/OfflineStates'
import Polling from '@/component/Polling'
import UpdateQueryString from '@/component/UpdateQueryString'

class Livewire {
    constructor(options = {}) {
        const defaults = {
            driver: 'http',
        }

        this.connection = new Connection
        this.components = componentStore
        this.onLoadCallback = () => {}
    }

    find(componentId) {
        return this.components.componentsById[componentId]
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

        this.setupAlpineCompatibility()

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

    rescan() {
        DOM.rootComponentElementsWithNoParents().forEach(el => {
            const componentId = el.getAttribute('id')
            if (this.components.hasComponent(componentId)) return

            this.components.addComponent(new Component(el, this.connection))
        })
    }

    plugin(callable) {
        callable(this)
    }

    requestIsOut() {
        return this.components.requestIsOut
    }

    setupAlpineCompatibility()
    {
        if (! window.Alpine) return

        if (window.Alpine.onBeforeComponentInitialized) {
            window.Alpine.onBeforeComponentInitialized(component => {
                let livewireEl = component.$el.closest('[wire\\:id]')

                if (livewireEl && livewireEl.__livewire) {
                    Object.entries(component.unobservedData).forEach(([key, value]) => {
                        if (value.livewireEntangle) {
                            let livewireProperty = value.livewireEntangle
                            let livewireComponent = livewireEl.__livewire

                            component.unobservedData[key] = livewireEl.__livewire.get(livewireProperty)

                            let preventSelfReaction = false

                            component.unobservedData.$watch(key, value => {
                                if (preventSelfReaction) { preventSelfReaction = false; return }

                                preventSelfReaction = true

                                // This prevents a "blip" when using x-model to set a Livewire property.
                                Alpine.ignoreFocusedForValueBinding = true

                                livewireComponent.set(livewireProperty, value)
                            })

                            livewireComponent.watch(livewireProperty, value => {
                                if (preventSelfReaction) { preventSelfReaction = false; return }

                                preventSelfReaction = true

                                component.$data[key] = value
                            })
                        }
                    })
                }
            })
        }

        if (window.Alpine.onComponentInitialized) {
            window.Alpine.onComponentInitialized(component => {
                let livewireEl = component.$el.closest('[wire\\:id]')

                if (livewireEl && livewireEl.__livewire) {
                    this.hook('afterDomUpdate', (livewireComponent) => {
                        if (livewireComponent === livewireEl.__livewire) {
                            component.updateElements(component.$el)

                            // This was set to true in the $wire Proxy's setter,
                            // Now we can re-set it to false.
                            Alpine.ignoreFocusedForValueBinding = false
                        }
                    })
                }
            })
        }

        if (window.Alpine.addMagicProperty) {
            window.Alpine.addMagicProperty('wire', function (componentEl) {
                let wireEl = componentEl.closest('[wire\\:id]')
                if (! wireEl) console.warn('Alpine: Cannot reference "\$wire" outside a Livewire component.')

                var refObj = {}

                return new Proxy(refObj, {
                    get (object, property) {
                        if (property === 'entangle') {
                           return name => ({ livewireEntangle: name })
                        }

                        // Forward public API methods right away.
                        if (['get', 'set', 'call', 'on'].includes(property)) {
                            return function(...args) {
                                return wireEl.__livewire[property].apply(wireEl.__livewire, args)
                            }
                        }

                        // If the property exists on the data, return it.
                        let getResult = wireEl.__livewire.get(property)

                        // If the property does not exist, try calling the method on the class.
                        if (getResult === undefined) {
                            return function(...args) {
                                return wireEl.__livewire.call.apply(wireEl.__livewire, [property, ...args])
                            }
                        }

                        return getResult
                    },

                    set: function(obj, prop, value) {
                        // This prevents a "blip" when using x-model to set a Livewire property.
                        Alpine.ignoreFocusedForValueBinding = true

                        wireEl.__livewire.set(prop, value)

                        return true
                    }
                })
            })
        }
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

UpdateQueryString()
OfflineStates()
LoadingStates()
DisableForms()
FileUploads()
FileDownloads()
DirtyStates()
Polling()

dispatch('livewire:available')

export default Livewire
