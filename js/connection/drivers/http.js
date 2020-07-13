import { getCsrfToken } from '@/util'

export default {
    onError: null,
    onMessage: null,

    init() {
        //
    },

    sendMessage(payload) {
        // Forward the query string for the ajax requests.
        fetch(`${window.livewire_app_url}/livewire/message/${payload.name}${window.location.search}`, {
            method: 'POST',
            body: JSON.stringify(payload),
            // This enables "cookies".
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/html, application/xhtml+xml',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Socket-ID': this.getSocketId(),
                'X-Livewire': true,
            },
        }).then(response => {
            if (response.ok) {
                response.text().then(response => {
                    if (this.isOutputFromDump(response)) {
                        this.onError(payload)
                        this.showHtmlModal(response)
                    } else {
                        this.onMessage.call(this, JSON.parse(response))
                    }
                })
            } else {
                if (this.onError(payload, response.status) === false) return

                if (response.status === 419) {
                    confirm("This page has expired due to inactivity.\nWould you like to refresh the page?")
                        && window.location.reload()
                } else {
                    response.text().then(response => {
                        this.showHtmlModal(response)
                    })
                }
            }
        }).catch(() => {
            this.onError(payload)
        })
    },

    isOutputFromDump(output) {
        return !! output.match(/<script>Sfdump\(".+"\)<\/script>/)
    },

    getSocketId() {
        if (typeof Echo !== 'undefined') {
            return Echo.socketId();
        }
    },

    // This code and concept is all Jonathan Reinink - thanks main!
    showHtmlModal(html) {
        let page = document.createElement('html')
        page.innerHTML = html
        page.querySelectorAll('a').forEach(a => a.setAttribute('target', '_top'))

        let modal = document.getElementById('burst-error');

        if(typeof(modal) != 'undefined' && modal != null){
            // Modal already exists.
            modal.innerHTML = ''
        } else {
            modal = document.createElement('div')
            modal.id = 'burst-error'
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
        modal.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.hideHtmlModal(modal) })
        modal.focus()
    },

    hideHtmlModal(modal) {
        modal.outerHTML = ''
        document.body.style.overflow = 'visible'
    },
}
