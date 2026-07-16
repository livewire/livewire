import { reconstructHtmlDelta } from '@/htmlDelta'
import { interceptMessage } from '@/request'
import { morph } from '@/morph'

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            let html = payload.effects.html
            let delta = payload.effects.htmlDelta
            let hash = payload.effects.htmlHash

            if (typeof html === 'string') {
                await morph(message.component, message.component.el, html)

                message.component.rememberServerRenderedHtml(html, hash)

                return
            }

            if (! delta) return

            if (message.component.serverRenderedHtml === null
                || message.component.serverRenderedHtmlHash !== delta.base
            ) {
                message.component.requestHtmlResync()

                return
            }

            try {
                html = await reconstructHtmlDelta(
                    message.component.serverRenderedHtml,
                    delta.patches ?? delta.patch,
                    hash,
                )
            } catch (error) {
                message.component.requestHtmlResync()

                return
            }

            await morph(message.component, message.component.el, html)

            message.component.rememberServerRenderedHtml(html, hash)
        })
    })
})
