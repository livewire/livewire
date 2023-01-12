import { trigger, on } from "../events";
import { closestComponent } from "../lifecycle";
import { dataGet, dataSet } from "../utils";

on('component.initialized', component => {
    let queryString = component.effects.queryString
    if (! queryString) return

    Object.entries(queryString).forEach(([key, value]) => {
        if (isNumeric(key)) {
            key = value

            // Handle normal queryString key.
            Alpine.persist(key, {
                get() {
                    return dataGet(component.dataReactive, key)
                },
                set(value) {
                    dataSet(component.dataReactive, key, value)
                },
            }, {
                getItem(key) {
                    let value = getFromQueryString(key)

                    return JSON.stringify(value)
                },
                setItem(key, value) {
                    pushToQueryString(key, JSON.parse(value))
                }
            })
        } else {
            // Handle queryString with exclude/default/as config.
        }
    })
})

function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

function getFromQueryString(key) {
    let url = new URL(window.location.href)

    let value = url.searchParams.get(key)

    if (value === 'true') return true
    if (value === 'false') return false

    return value
}

function pushToQueryString(key, value) {
    let url = new URL(window.location.href)

    url.searchParams.set(key, value)

    window.history.replaceState({}, '', url.toString())
}
