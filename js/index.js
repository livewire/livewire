import '@/dom/polyfills/index'
import componentStore from '@/Store'
import DOM from '@/dom/dom'
import Component from '@/component/index'
import Connection from '@/connection'
import { dispatch } from './util'
import FileUploads from '@/component/FileUploads'
import FileDownloads from '@/component/FileDownloads'
import LoadingStates from '@/component/LoadingStates'
import LaravelEcho from '@/component/LaravelEcho'
import DisableForms from '@/component/DisableForms'
import DirtyStates from '@/component/DirtyStates'
import OfflineStates from '@/component/OfflineStates'
import Polling from '@/component/Polling'
import UpdateQueryString from '@/component/UpdateQueryString'

class Livewire {
    constructor() {
        this.connection = new Connection()
        this.components = componentStore
        this.onLoadCallback = () => {}
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

    setupAlpineCompatibility() {
        if (!window.Alpine) return

        if (window.Alpine.onBeforeComponentInitialized) {
            window.Alpine.onBeforeComponentInitialized(component => {
                let livewireEl = component.$el.closest('[wire\\:id]')

                if (livewireEl && livewireEl.__livewire) {
                    Object.entries(component.unobservedData).forEach(
                        ([key, value]) => {
                            if (
                                !!value &&
                                typeof value === 'object' &&
                                value.livewireEntangle
                            ) {
                                // Ok, it looks like someone set an Alpine property to $wire.entangle or @entangle.
                                let livewireProperty = value.livewireEntangle
                                let isDeferred = value.isDeferred
                                let livewireComponent = livewireEl.__livewire

                                // Let's set the initial value of the Alpine prop to the Livewire prop's value.
                                component.unobservedData[
                                    key
                                ] = livewireEl.__livewire.get(livewireProperty)

                                // Now, we'll watch for changes to the Alpine prop, and fire the update to Livewire.
                                component.unobservedData.$watch(key, value => {
                                    // If the Alpine value is the same as the Livewire value, we'll skip the update for 2 reasons:
                                    // - It's just more efficient, why send needless requests.
                                    // - This prevents a circular dependancy with the other watcher below.
                                    if (
                                        value ===
                                        livewireEl.__livewire.get(
                                            livewireProperty
                                        )
                                    )
                                        return

                                    // We'll tell Livewire to update the property, but we'll also tell Livewire
                                    // to not call the normal property watchers on the way back to prevent another
                                    // circular dependancy.
                                    livewireComponent.set(
                                        livewireProperty,
                                        value,
                                        isDeferred,
                                        true // Skip firing Livewire watchers when the request comes back.
                                    )
                                })

                                // We'll also listen for changes to the Livewire prop, and set them in Alpine.
                                livewireComponent.watch(
                                    livewireProperty,
                                    value => {
                                        component.$data[key] = value
                                    }
                                )
                            }
                        }
                    )
                }
            })
        }

        if (window.Alpine.onComponentInitialized) {
            window.Alpine.onComponentInitialized(component => {
                let livewireEl = component.$el.closest('[wire\\:id]')

                if (livewireEl && livewireEl.__livewire) {
                    this.hook('afterDomUpdate', livewireComponent => {
                        if (livewireComponent === livewireEl.__livewire) {
                            component.updateElements(component.$el)
                        }
                    })
                }
            })
        }

        if (window.Alpine.addMagicProperty) {
            window.Alpine.addMagicProperty('wire', function (componentEl) {
                let wireEl = componentEl.closest('[wire\\:id]')

                if (!wireEl)
                    console.warn(
                        'Alpine: Cannot reference "$wire" outside a Livewire component.'
                    )

                let component = wireEl.__livewire

                return component.$wire
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
LaravelEcho()
FileDownloads()
DirtyStates()
Polling()

dispatch('livewire:available')

export default Livewire
