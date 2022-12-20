import { findComponent, state } from "../../../js/state";
import { on } from '../../../js/synthetic/index'
import Alpine from 'alpinejs'

export default function (enabled) {
    on('decorate', (target, path, addProp, decorator, symbol) => {
        addProp('entangle', (name, defer = true) => {
            let component = findComponent(target.__livewireId)

            return generateEntangleFunction(component)(name, defer)
        })
    })
}

function generateEntangleFunction(component) {
    return (name, defer = true) => {
        let isDeferred = defer
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

            Alpine.entangle({
                // Outer scope...
                get() {
                    return livewireComponent.get(name)
                },
                set(value) {
                    livewireComponent.set(name, value)
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


            return livewireComponent.get(name)
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
