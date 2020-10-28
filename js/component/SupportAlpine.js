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
                                livewireEl.__livewire.getPropertyValueIncludingDefers(
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

export function alpinifyElementsForMorphdom(from, to) {
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
                to.setAttribute('style', style.replace('display: none;', ''))
            } else if (isShowing(from, to)) {
                to.style.display = from.style.display
            }
        }
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
