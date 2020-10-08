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

class Livewire {
    constructor() {
        this.connection = new Connection()
        this.components = store
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

    rescan(node = null) {
        DOM.rootComponentElementsWithNoParents(node).forEach(el => {
            const componentId = wireDirectives(el).get('id').value

            if (this.components.hasComponent(componentId)) return

            this.components.addComponent(new Component(el, this.connection))
        })
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
                                component.unobservedData[key]
                                    // We need to stringify and parse it though to get a deep clone.
                                    = JSON.parse(JSON.stringify(livewireEl.__livewire.get(livewireProperty)))

                                let blockAlpineWatcher = false

                                // Now, we'll watch for changes to the Alpine prop, and fire the update to Livewire.
                                component.unobservedData.$watch(key, value => {
                                    // Let's also make sure that this watcher isn't a result of a Livewire response.
                                    // If it is, we don't need to "re-update" Livewire. (sending an extra useless) request.
                                    if (blockAlpineWatcher === true) {
                                        blockAlpineWatcher = false
                                        return
                                    }

                                    // If the Alpine value is the same as the Livewire value, we'll skip the update for 2 reasons:
                                    // - It's just more efficient, why send needless requests.
                                    // - This prevents a circular dependancy with the other watcher below.
                                    if (
                                        value ===
                                        livewireEl.__livewire.get(
                                            livewireProperty
                                        )
                                    ) return

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
                                        blockAlpineWatcher = true
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
                    this.hook('message.processed', livewireComponent => {
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

SyncBrowserHistory()
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
