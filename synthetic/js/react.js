import { effect, synthetic } from './index'
import { useState, useEffect } from 'react'
import { isObjecty } from './utils'

/**
 * This is a react hook for using synthetic objects in
 * React. We are still using Vue's reactivity engine
 * under the hood, and forcing React to re-render.
 */
export function useSynthetic(raw) {
    // Our "force render" hack...
    let setTick = useState(0)[1]
    let rerender = () => setTick(+new Date())

    let [syntheticObj] = useState(() => synthetic(raw))

    useEffect(() => {
        let effectRef = effect(() => {
            // Deeply "touch" every reactive property
            // so this effect callback will re-run
            // anytime the synthetic is changed.
            deeplyReadObject(syntheticObj)

            rerender()
        })

        return () => release(effectRef)
    }, []) // Using "[]" so that this only runs on initialization.

    return syntheticObj
}

function deeplyReadObject(obj) {
    isObjecty(obj) && Object.entries(obj).forEach(([key]) => {
        let throwaway = deeplyReadObject(obj[key])
    })
}
