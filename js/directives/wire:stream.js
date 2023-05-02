import { directive } from '@/directives'
import { on, trigger } from '@/events'
import { contentIsFromDump } from '@/request'

directive('stream', (el, { expression, modifiers }, { component, cleanup }) => {
    let off = on('stream', ({ name, content, append }) => {
        if (name !== expression) return

        if (modifiers.includes('append') || append) {
            el.innerHTML = el.innerHTML + content
        } else {
            el.innerHTML = content
        }
    })

    cleanup(off)
})

on('fetch', () => {
    return response => {
        if (! response.headers.has('X-Livewire-Stream')) return response

        return {
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
    }
})

async function interceptStreamAndReturnFinalResponse(response, callback) {
    let reader = response.body.getReader()
    let finalResponse = ''

    while (true) {
        let { done, value: chunk } = await reader.read()

        let decoder = new TextDecoder
        let output = decoder.decode(chunk)

        if (output && output.startsWith('{"stream":true')) {
            callback(JSON.parse(output).body)
        } else {
            finalResponse = finalResponse + output
        }

        if (done) return finalResponse
    }
}
