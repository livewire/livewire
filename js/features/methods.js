import { dataGet, each } from '@/utils'
import { on } from "@/events"
import { callMethod } from "../request"

// Pure "js" methods...
// on('decorate', (component, path, addProp) => {
//     let effects = component.effects

//     if (! effects) return

//     let methods = effects['js'] || []

//     let AsyncFunction = Object.getPrototypeOf(async function(){}).constructor

//     each(methods, (name, expression) => {
//         let func = new AsyncFunction([], expression)

//         addProp(name, () => {
//             func.bind(dataGet(component.reactive, path))()
//         })
//     })
// })
