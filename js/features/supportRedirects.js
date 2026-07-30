import { on } from "@/hooks"
import { shouldRedirectUsingNavigateOr } from "./supportNavigate"

on('effect', ({ effects, request }) => {
    if (! Object.hasOwn(effects, 'redirect') || ! effects['redirect']) return

    let preventDefault = false

    request.invokeOnRedirect({ url: effects['redirect'], preventDefault: () => preventDefault = true })

    if (preventDefault) return

    let url = effects['redirect']

    shouldRedirectUsingNavigateOr(effects, url, () => {
        window.location.href = url
    })
})
