import { morph } from "@/morph";
import { interceptMessage } from "@/request";

interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
        onMorph(() => {
            let html = payload.effects.html

            if (! html) return

            morph(message.component, message.component.el, html)
        })
    })
})
