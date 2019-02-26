export default {
    onMessage: null,
    lastTimeARequestWasSent: null,

    init() {
        //
    },

    sendMessage(payload, minWait) {
        var timestamp = (new Date()).valueOf();
        this.lastTimeARequestWasSent = timestamp;

        // @todo - Figure out not relying on app's csrf stuff in bootstrap.js
        const token = document.head.querySelector('meta[name="csrf-token"]').content

        Promise.all([
            fetch('/livewire/message', {
                method: 'POST',
                body: JSON.stringify(payload),
                // This enables "cookies".
                credentials: "same-origin",
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'text/html, application/xhtml+xml',
                    // "Accept": "application/json, text-plain, */*",
                },
            }),
            new Promise(resolve => setTimeout(resolve, minWait || 0)),
        ]).then(([response]) => {
            if (timestamp < this.lastTimeARequestWasSent) {
                return
            }

            window.response = response

            if (response.ok) {
                response.text().then(response => {
                    this.onMessage.call(this, JSON.parse(response))
                })
            } else {
                response.text().then(response => {
                    var iframe = document.createElement('iframe');
                    var wrapper = document.createElement('div');
                    wrapper.classList.add('absolute', 'pin', 'p-8', 'overflow-none')
                    iframe.classList.add('w-full', 'h-full', 'rounded', 'shadow')
                    document.body.appendChild(wrapper);
                    wrapper.appendChild(iframe)
                    iframe.contentWindow.document.open();
                    iframe.contentWindow.document.write(response);
                    iframe.contentWindow.document.close();
                })
            }
        })
            // @todo: catch 419 session expired.
    },
}
