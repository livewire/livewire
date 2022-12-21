import { findComponent, state } from "../../../js/state";
import { on } from '../../../js/synthetic/index'
import Alpine from 'alpinejs'

export default function (enabled) {
    on('decorate', (target, path, addProp, decorator, symbol) => {
        addProp('entangle', (name, live = false) => {
            let component = findComponent(target.__livewireId)

            return generateEntangleFunction(component)(name, live)
        })
    })
}

function generateEntangleFunction(component) {
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
                        console.log('livewire get', livewireComponent.get(name))
                        return livewireComponent.get(name)
                    },
                    set(value) {
                        console.log('livewire set', value, isLive)
                        livewireComponent.set(name, value, isLive)
                    }
                }, {
                    // Inner scope...
                    get() {
                        console.log('alpine get', getter())
                        return getter()
                    },
                    set(value) {
                        console.log('alpine set', value)
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
