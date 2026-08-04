import { on } from "@/hooks"
import { shouldRedirectUsingNavigateOr } from "./supportNavigate"

on('effect', ({ effects }) => {
    if (! Object.prototype.hasOwnProperty.call(effects, 'redirect') || ! effects['redirect']) return

    let url = effects['redirect']

    shouldRedirectUsingNavigateOr(effects, url, () => {
        window.location.href = url
    })
})
