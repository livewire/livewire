import { findComponent } from "../store";
import { on } from '@/events'
import Alpine from 'alpinejs'

export function generateEntangleFunction(component, cleanup) {
    console.log('generateEntangleFunction', cleanup)
    if (! cleanup) cleanup = () => {}

    return (name, live) => {
        let isLive = live
        let livewireProperty = name
        let livewireComponent = component.$wire
        let livewirePropertyValue = livewireComponent.get(livewireProperty)

        let interceptor = Alpine.interceptor((initialValue, getter, setter, path, key) => {
            // Check to see if the Livewire property exists and if not log a console error
            // and return so everything else keeps running.
            if (typeof livewirePropertyValue === 'undefined') {
                console.error(`Livewire Entangle Error: Livewire property ['${livewireProperty}'] cannot be found on component: ['${component.name}']`)
                return
            }

            let release = Alpine.entangle({
                // Outer scope...
                get() {
                    console.log('entangleOuterGet', name, livewireComponent.get(name))
                    return livewireComponent.get(name)
                },
                set(value) {
                    console.log('entangleOuterSet', name, value)
                    livewireComponent.set(name, value, isLive)
                }
            }, {
                // Inner scope...
                get() {
                    console.log('entangleInnerGet', name, getter())
                    return getter()
                },
                set(value) {
                    console.log('entangleInnerSet', name, value)
                    setter(value)
                }
            })

            console.log('release', name, release)

            cleanup(() => {
                console.log('cleanUp', name)
                release()
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
