import { morph } from "@/morph";
import { interceptMessage } from "@/request";

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(async () => {
            let html = payload.effects.html

            if (! html) return

            await morph(message.component, message.component.el, html)
        })
    })
})
