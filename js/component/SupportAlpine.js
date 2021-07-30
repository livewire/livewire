import { walk } from './../util/walk'
import store from '@/Store'

export default function () {
    window.addEventListener('livewire:load', () => {
        if (! window.Alpine) return

        refreshAlpineAfterEveryLivewireRequest()

        addDollarSignWire()

        supportEntangle()
    })
}

function refreshAlpineAfterEveryLivewireRequest() {
    if (isV3()) {
        store.registerHook('message.processed', (message, livewireComponent) => {
            walk(livewireComponent.el, el => {
                if (el._x_hidePromise) return
                if (el._x_runEffects) el._x_runEffects()
            })
        })

        return
    }

    if (! window.Alpine.onComponentInitialized) return

    window.Alpine.onComponentInitialized(component => {
        let livewireEl = component.$el.closest('[wire\\:id]')

        if (livewireEl && livewireEl.__livewire) {
            store.registerHook('message.processed', (message, livewireComponent) => {
                if (livewireComponent === livewireEl.__livewire) {
                    component.updateElements(component.$el)
                }
            })
        }
    })
}

function addDollarSignWire() {
    if (isV3()) {
        window.Alpine.magic('wire', function (el) {
            let wireEl = el.closest('[wire\\:id]')

            if (! wireEl)
                console.warn(
                    'Alpine: Cannot reference "$wire" outside a Livewire component.'
                )

            let component = wireEl.__livewire

            return component.$wire
        })
        return
    }

    if (! window.Alpine.addMagicProperty) return

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

function supportEntangle() {
    if (isV3()) return

    if (! window.Alpine.onBeforeComponentInitialized) return

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

                        let livewirePropertyValue = livewireEl.__livewire.get(livewireProperty)

                        // Check to see if the Livewire property exists and if not log a console error
                        // and return so everything else keeps running.
                        if (typeof livewirePropertyValue === 'undefined') {
                            console.error(`Livewire Entangle Error: Livewire property '${livewireProperty}' cannot be found`)
                            return
                        }

                        // Let's set the initial value of the Alpine prop to the Livewire prop's value.
                        component.unobservedData[key]
                            // We need to stringify and parse it though to get a deep clone.
                            = JSON.parse(JSON.stringify(livewirePropertyValue))

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
                            // - Due to the deep clone using stringify, we need to do the same here to compare.
                            if (
                                JSON.stringify(value) ==
                                JSON.stringify(
                                    livewireEl.__livewire.getPropertyValueIncludingDefers(
                                        livewireProperty
                                    )
                                )
                            ) return

                            // We'll tell Livewire to update the property, but we'll also tell Livewire
                            // to not call the normal property watchers on the way back to prevent another
                            // circular dependancy.
                            livewireComponent.set(
                                livewireProperty,
                                value,
                                isDeferred,
                                // Block firing of Livewire watchers for this data key when the request comes back.
                                // Unless it is deferred, in which cause we don't know if the state will be the same, so let it run.
                                isDeferred ? false : true
                            )
                        })

                        // We'll also listen for changes to the Livewire prop, and set them in Alpine.
                        livewireComponent.watch(
                            livewireProperty,
                            value => {
                                // Ensure data is deep cloned otherwise Alpine mutates Livewire data
                                component.$data[key] = typeof value !== 'undefined' ? JSON.parse(JSON.stringify(value)) : value
                            }
                        )
                    }
                }
            )
        }
    })
}

export function getEntangleFunction(component) {
    if (isV3()) {
        return (name, defer = false) => {
            let isDeferred = defer
            let livewireProperty = name
            let livewireComponent = component
            let livewirePropertyValue = component.get(livewireProperty)

            let interceptor = Alpine.interceptor((initialValue, getter, setter, path, key) => {
                // Check to see if the Livewire property exists and if not log a console error
                // and return so everything else keeps running.
                if (typeof livewirePropertyValue === 'undefined') {
                    console.error(`Livewire Entangle Error: Livewire property '${livewireProperty}' cannot be found`)
                    return
                }

                // Let's set the initial value of the Alpine prop to the Livewire prop's value.
                let value
                    // We need to stringify and parse it though to get a deep clone.
                    = JSON.parse(JSON.stringify(livewirePropertyValue))

                setter(value)

                // Now, we'll watch for changes to the Alpine prop, and fire the update to Livewire.
                window.Alpine.effect(() => {
                    let value = getter()

                    if (
                        JSON.stringify(value) ==
                        JSON.stringify(
                            livewireComponent.getPropertyValueIncludingDefers(
                                livewireProperty
                            )
                        )
                    ) return

                    // We'll tell Livewire to update the property, but we'll also tell Livewire
                    // to not call the normal property watchers on the way back to prevent another
                    // circular dependancy.
                    livewireComponent.set(
                        livewireProperty,
                        value,
                        isDeferred,
                        // Block firing of Livewire watchers for this data key when the request comes back.
                        // Unless it is deferred, in which cause we don't know if the state will be the same, so let it run.
                        isDeferred ? false : true
                    )
                })

                // We'll also listen for changes to the Livewire prop, and set them in Alpine.
                livewireComponent.watch(
                    livewireProperty,
                    value => {
                        // Ensure data is deep cloned otherwise Alpine mutates Livewire data
                        window.Alpine.disableEffectScheduling(() => {
                            setter(typeof value !== 'undefined' ? JSON.parse(JSON.stringify(value)) : value)
                        })
                    }
                )

                return value
            }, obj => {
                Object.defineProperty(obj, 'defer', {
                    get() {
                        isDeferred = true

                        return obj
                    }
                })
            })

            return interceptor(livewirePropertyValue)
        }
    }

    return (name, defer = false) => ({
        isDeferred: defer,
        livewireEntangle: name,
        get defer() {
            this.isDeferred = true
            return this
        },
    })
}

export function alpinifyElementsForMorphdom(from, to) {
    if (isV3()) {
        return alpinifyElementsForMorphdomV3(from, to)
    }

    // If the element we are updating is an Alpine component...
    if (from.__x) {
        // Then temporarily clone it (with it's data) to the "to" element.
        // This should simulate backend Livewire being aware of Alpine changes.
        window.Alpine.clone(from.__x, to)
    }

    // x-show elements require care because of transitions.
    if (
        Array.from(from.attributes)
            .map(attr => attr.name)
            .some(name => /x-show/.test(name))
    ) {
        if (from.__x_transition) {
            // This covers @entangle('something')
            from.skipElUpdatingButStillUpdateChildren = true
        } else {
            // This covers x-show="$wire.something"
            //
            // If the element has x-show, we need to "reverse" the damage done by "clone",
            // so that if/when the element has a transition on it, it will occur naturally.
            if (isHiding(from, to)) {
                let style = to.getAttribute('style')

                if (style) {
                    to.setAttribute('style', style.replace('display: none;', ''))
                }
            } else if (isShowing(from, to)) {
                to.style.display = from.style.display
            }
        }
    }
}

function alpinifyElementsForMorphdomV3(from, to) {
    if (from.nodeType !== 1) return

    // If the element we are updating is an Alpine component...
    if (from._x_dataStack) {
        // Then temporarily clone it (with it's data) to the "to" element.
        // This should simulate backend Livewire being aware of Alpine changes.
        window.Alpine.clone(from, to)
    }
}

function isHiding(from, to) {
    if (beforeAlpineTwoPointSevenPointThree()) {
        return from.style.display === '' && to.style.display === 'none'
    }

    return from.__x_is_shown && ! to.__x_is_shown
}

function isShowing(from, to) {
    if (beforeAlpineTwoPointSevenPointThree()) {
        return from.style.display === 'none' && to.style.display === ''
    }

    return ! from.__x_is_shown && to.__x_is_shown
}

function beforeAlpineTwoPointSevenPointThree() {
    let [major, minor, patch] = window.Alpine.version.split('.').map(i => Number(i))

    return major <= 2 && minor <= 7 && patch <= 2
}

function isV3() {
    return window.Alpine && window.Alpine.version && /^3\..+\..+$/.test(window.Alpine.version)
}
