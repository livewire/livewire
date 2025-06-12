import { findComponent, hasComponent } from '@/store'
import { contentIsFromDump } from '@/utils'
import { directive } from '@/directives'
import { on, trigger } from '@/hooks'

on('stream', (payload) => {
    if (payload.type !== 'update') return

    let { id, key, value, mode } = payload

    if (! hasComponent(id)) return

    let component = findComponent(id)

    if (mode === 'append') {
        component.$wire.set(key, component.$wire.get(key) + value, false)
    } else {
        component.$wire.set(key, value, false)
    }
})

directive('stream', ({el, directive, cleanup }) => {
    let { expression, modifiers } = directive

    let off = on('stream', (payload) => {
        // Default type is "html" becasue that was the original stream feature...
        payload.type = payload.type || 'html'

        if (payload.type !== 'html') return

        let { name, content, mode } = payload

        if (name !== expression) return

        if (modifiers.includes('replace') || mode === 'replace') {
            el.innerHTML = content
        } else {
            el.insertAdjacentHTML('beforeend', content)
        }
    })

    cleanup(off)
})

on('request', ({ respond }) => {
    respond(mutableObject => {
        let response = mutableObject.response

        if (! response.headers.has('X-Livewire-Stream')) return

        mutableObject.response = {
            ok: true,
            redirected: false,
            status: 200,

            async text() {
                let finalResponse = ''

                try {
                    finalResponse = await interceptStreamAndReturnFinalResponse(response, streamed => {
                        trigger('stream', streamed)
                    })
                } catch (e) {
                    this.aborted = true
                    this.ok = false
                }

                if (contentIsFromDump(finalResponse)) {
                    this.ok = false
                }

                return finalResponse
            }
        }
    })
})

async function interceptStreamAndReturnFinalResponse(response, callback) {
    let reader = response.body.getReader()
    let remainingResponse = ''

    while (true) {
        let { done, value: chunk } = await reader.read()

        let decoder = new TextDecoder
        let output = decoder.decode(chunk)

        let [ streams, remaining ] = extractStreamObjects(remainingResponse + output)

        streams.forEach(stream => {
            callback(stream)
        })

        remainingResponse = remaining

        if (done) return remainingResponse
    }
}

function extractStreamObjects(raw) {
    let regex = /({"stream":true.*?"endStream":true})/g

    let matches = raw.match(regex)

    let parsed = []

    if (matches) {
        for (let i = 0; i < matches.length; i++) {
            parsed.push(JSON.parse(matches[i]).body)
        }
    }

    let remaining = raw.replace(regex, '');

    return [ parsed, remaining ];
}
