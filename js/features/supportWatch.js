import { dataGet } from '@/utils'
import Alpine from 'alpinejs'

export function generateWatchFunction(component, cleanup) {
    return (path, callback) => {
        let getter = () => dataGet(component.reactive, path)

        let unwatch = Alpine.watch(getter, callback)

        if (cleanup) {
            cleanup(unwatch)

            return unwatch
        }

        let removeCleanup = component.addCleanup(unwatch)

        return () => {
            removeCleanup()
            unwatch()
        }
    }
}
