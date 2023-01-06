// ----------------------------------
// <div wire:loading>Loading...</div>
// ----------------------------------

let handleRequestAndResponse = (a, b) => {}
let setLoading = () => {}

function init() {
    let directive = 'wire:loading.remove.delay'

    let invert = directive.includes('.remove') ? i => ! i : i => i
    let [delay, interupt] = applyDelay(directive)

    let load = setLoading(invert(true))
    let unload = setLoading(invert(false))

    unload()

    onRequest(() => {
        delay(load)

        return () => interupt(unload)
    })
}



















function onRequest(callback) {
    let responseHandler

    handleRequestAndResponse(
        () => responseHandler = callback(),
        responseHandler,
    )
}

function applyDelay(directive, duration = 200) {
    if (! directive.includes('.delay')) {
        return [i => i(), i => i()]
    }

    let interupt, started = false

    return [
        callback => {
            let timeout = setTimeout(() => {
                callback()

                started = true
            }, duration)

            interupt = () => clearTimeout(timeout)
        },
        callback => started ? callback() : interupt(),
    ]
}



