import { findComponent } from "../store";
import { on } from '@/events'
import Alpine from 'alpinejs'

export function generateEntangleFunction(component) {
    return (name, live) => {
        let isLive = live
        let livewireProperty = name
        let livewireComponent = component.$wire
        let livewirePropertyValue = livewireComponent.get(livewireProperty)

        let interceptor = Alpine.interceptor((initialValue, getter, setter, path, key) => {
            // Check to see if the Livewire property exists and if not log a console error
            // and return so everything else keeps running.
            if (typeof livewirePropertyValue === 'undefined') {
                console.error(`Livewire Entangle Error: Livewire property '${livewireProperty}' cannot be found`)
                return
            }

            queueMicrotask(() => {
                Alpine.entangle({
                    // Outer scope...
                    get() {
                        return livewireComponent.get(name)
                    },
                    set(value) {
                        livewireComponent.set(name, value, isLive)
                    }
                }, {
                    // Inner scope...
                    get() {
                        return getter()
                    },
                    set(value) {
                        setter(value)
                    }
                })
            })

            return livewireComponent.get(name)
        }, obj => {
            Object.defineProperty(obj, 'live', {
                get() {
                    isLive = true

                    return obj
                }
            })
        })

        return interceptor(livewirePropertyValue)
    }
}
