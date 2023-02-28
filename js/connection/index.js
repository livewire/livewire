import store from '@/Store'
import componentStore from '../Store'
import { getCsrfToken } from '@/util'

export default class Connection {
    constructor() {
        this.headers = {}
    }

    onMessage(message, payload) {
        message.component.receiveMessage(message, payload)
    }

    onError(message, status, response) {
        message.component.messageSendFailed()

        return componentStore.onErrorCallback(status, response)
    }

    showExpiredMessage(response, message) {
        if (store.sessionHasExpiredCallback) {
            store.sessionHasExpiredCallback(response, message)
        } else {
            confirm(
                'This page has expired.\nWould you like to refresh the page?'
            ) && window.location.reload()
        }
    }

    sendMessage(message) {
        let payload = message.payload()
        let csrfToken = getCsrfToken()
        let socketId = this.getSocketId()
        let appUrl = window.livewire_app_url

        if (this.shouldUseLocalePrefix(payload)) {
            appUrl = `${appUrl}/${payload.fingerprint.locale}`
        }


        if (window.__testing_request_interceptor) {
            return window.__testing_request_interceptor(payload, this)
        }

        // Forward the query string for the ajax requests.
        fetch(
            `${appUrl}/livewire/message/${payload.fingerprint.name}`,
            {
                method: 'POST',
                body: JSON.stringify(payload),
                // This enables "cookies".
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/html, application/xhtml+xml',
                    'X-Livewire': true,

                    // set Custom Headers
                    ...(this.headers),

                    // We'll set this explicitly to mitigate potential interference from ad-blockers/etc.
                    'Referer': window.location.href,
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken }),
                    ...(socketId && { 'X-Socket-ID': socketId })
                },
            }
        )
            .then(response => {
                if (response.ok) {
                    response.text().then(response => {
                        if (this.isOutputFromDump(response)) {
                            this.onError(message)
                            this.showHtmlModal(response)
                        } else {
                            this.onMessage(message, JSON.parse(response))
                        }
                    })
                } else {
                    if (this.onError(message, response.status, response) === false) return

                    if (response.status === 419) {
                        if (store.sessionHasExpired) return

                        store.sessionHasExpired = true

                        this.showExpiredMessage(response, message)
                    } else {
                        response.text().then(response => {
                            this.showHtmlModal(response)
                        })
                    }
                }
            })
            .catch(() => {
                this.onError(message)
            })
    }

    shouldUseLocalePrefix(payload) {
        let path = payload.fingerprint.path
        let locale = payload.fingerprint.locale

        if (path.split('/')[0] == locale) {
            return true
        }

        return false
    }

    isOutputFromDump(output) {
        return !!output.match(/<script>Sfdump\(".+"\)<\/script>/)
    }

    getSocketId() {
        if (typeof Echo !== 'undefined') {
            return Echo.socketId()
        }
    }

    // This code and concept is all Jonathan Reinink - thanks main!
    showHtmlModal(html) {
        let page = document.createElement('html')
        page.innerHTML = html
        page.querySelectorAll('a').forEach(a =>
            a.setAttribute('target', '_top')
        )

        let modal = document.getElementById('livewire-error')

        if (typeof modal != 'undefined' && modal != null) {
            // Modal already exists.
            modal.innerHTML = ''
        } else {
            modal = document.createElement('div')
            modal.id = 'livewire-error'
            modal.style.position = 'fixed'
            modal.style.width = '100vw'
            modal.style.height = '100vh'
            modal.style.padding = '50px'
            modal.style.backgroundColor = 'rgba(0, 0, 0, .6)'
            modal.style.zIndex = 200000
        }

        let iframe = document.createElement('iframe')
        iframe.style.backgroundColor = '#17161A'
        iframe.style.borderRadius = '5px'
        iframe.style.width = '100%'
        iframe.style.height = '100%'
        modal.appendChild(iframe)

        document.body.prepend(modal)
        document.body.style.overflow = 'hidden'
        iframe.contentWindow.document.open()
        iframe.contentWindow.document.write(page.outerHTML)
        iframe.contentWindow.document.close()

        // Close on click.
        modal.addEventListener('click', () => this.hideHtmlModal(modal))

        // Close on escape key press.
        modal.setAttribute('tabindex', 0)
        modal.addEventListener('keydown', e => {
            if (e.key === 'Escape') this.hideHtmlModal(modal)
        })
        modal.focus()
    }

    hideHtmlModal(modal) {
        modal.outerHTML = ''
        document.body.style.overflow = 'visible'
    }
}
