import { contentIsFromDump } from '@/utils'
import { directive } from '@/directives'
import { on, trigger } from '@/events'

directive('stream', ({el, directive, component, cleanup }) => {
    let { expression, modifiers } = directive

    let off = on('stream', ({ name, content, replace }) => {
        if (name !== expression) return

        if (modifiers.includes('replace') || replace) {
            el.innerHTML = content
        } else {
            el.innerHTML = el.innerHTML + content
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
                let finalResponse = await interceptStreamAndReturnFinalResponse(response, streamed => {
                    trigger('stream', streamed)
                })

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
    let finalResponse = ''

    while (true) {
        let { done, value: chunk } = await reader.read()

        let decoder = new TextDecoder
        let output = decoder.decode(chunk)

        let [ streams, remaining ] = extractStreamObjects(output)

        streams.forEach(stream => {
            callback(stream)
        })

        finalResponse = finalResponse + remaining

        if (done) return finalResponse
    }
}

function extractStreamObjects(raw) {
    let regex = /({"stream":true[\s\S]*?"endStream":true})/g

    let matches = raw.match(regex)

    let parsed = []

    if (matches) {
        for (let i = 0; i < matches.length; i++) {
            parsed.push(JSON.parse(matches[i].replace(/[\n|\r]/g, '\\n')).body)
        }
    }

    let remaining = raw.replace(regex, '');

    return [ parsed, remaining ];
}
