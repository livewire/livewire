import store from '@/Store'
import componentStore from '../Store'
import { getCsrfToken } from '@/util'

export default class Connection {
    onMessage(message, payload) {
        message.component.receiveMessage(message, payload)
    }

    onError(message, status) {
        message.component.messageSendFailed()

        return componentStore.onErrorCallback(status)
    }

    showExpiredMessage() {
        confirm(
            'This page has expired due to inactivity.\nWould you like to refresh the page?'
        ) && window.location.reload()
    }

    sendMessage(message) {
        let payload = message.payload()
        let csrfToken = getCsrfToken()
        let socketId = this.getSocketId()

        if (window.__testing_request_interceptor) {
            return window.__testing_request_interceptor(payload, this)
        }

        // Forward the query string for the ajax requests.
        fetch(
            `${window.livewire_app_url}/livewire/message/${payload.fingerprint.name}`,
            {
                method: 'POST',
                body: JSON.stringify(payload),
                // This enables "cookies".
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/html, application/xhtml+xml',
                    'X-Livewire': true,

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
                    if (this.onError(message, response.status) === false) return

                    if (response.status === 419) {
                        if (store.sessionHasExpired) return

                        store.sessionHasExpired = true

                        this.showExpiredMessage()
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
