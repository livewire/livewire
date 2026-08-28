import { deepClone, deeplyEqual, isObject } from '@/utils'

export function mergeCleanState(baseline, reactive, canonical) {
    if (deeplyEqual(baseline, reactive)) return deepClone(canonical)

    if (! isObject(baseline) || ! isObject(reactive) || ! isObject(canonical)) {
        return deepClone(baseline)
    }

    let merged = {}
    let keys = new Set([
        ...Object.keys(baseline),
        ...Object.keys(reactive),
        ...Object.keys(canonical),
    ])

    keys.forEach(key => {
        let baselineHasKey = Object.hasOwn(baseline, key)
        let reactiveHasKey = Object.hasOwn(reactive, key)
        let canonicalHasKey = Object.hasOwn(canonical, key)

        let isClean = baselineHasKey === reactiveHasKey
            && (! baselineHasKey || deeplyEqual(baseline[key], reactive[key]))

        if (isClean) {
            if (canonicalHasKey) merged[key] = deepClone(canonical[key])

            return
        }

        if (baselineHasKey && reactiveHasKey && canonicalHasKey
            && isObject(baseline[key]) && isObject(reactive[key]) && isObject(canonical[key])) {
            merged[key] = mergeCleanState(baseline[key], reactive[key], canonical[key])

            return
        }

        if (baselineHasKey) merged[key] = deepClone(baseline[key])
    })

    return merged
}
