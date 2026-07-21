import { fireAction, interceptMessage } from '@/request'
import { morph } from '@/morph'

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            let html = payload.effects.html
            let render = payload.effects.render
            let hash = render?.target || payload.effects.htmlHash

            if (payload.effects.renderRecovery) {
                await message.component.requestHtmlResync(() => {
                    return fireAction(
                        message.component,
                        '$refresh',
                        [],
                        { async: true, transportRecovery: true },
                    )
                })

                return
            }

            if (typeof html !== 'string') return

            if (message.component.serverRenderedHtmlHash !== hash) {
                await morph(message.component, message.component.el, html)
            }

            message.component.rememberServerRenderedHtml(
                html,
                hash,
                render,
                message.renderBaseline,
                message.renderAttemptedPortable,
            )
        })
    })
})
