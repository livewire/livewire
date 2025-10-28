import { on } from "@/hooks"
import { shouldRedirectUsingNavigateOr } from "./supportNavigate"

on('effect', ({ effects, request }) => {
    if (! effects['redirect']) return

    let preventDefault = false

    request.onRedirect({ url: effects['redirect'], preventDefault: () => preventDefault = true })

    if (preventDefault) return

    let url = effects['redirect']

    shouldRedirectUsingNavigateOr(effects, url, () => {
        window.location.href = url
    })
})
