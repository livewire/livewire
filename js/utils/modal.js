
// This code and concept is all Jonathan Reinink - thanks man!
export function showHtmlModal(html) {
    let page = document.createElement('html')
    page.innerHTML = html
    page.querySelectorAll('a').forEach(a =>
        a.setAttribute('target', '_top')
    )

    let modal = document.getElementById('livewire-error')

    if (typeof modal != 'undefined' && modal != null) {
        // Modal already exists and is open, because closing it removes it from the DOM.
        // Only replace its content: moving an open modal dialog in the DOM takes it out
        // of the top layer, leaving it open as a non-modal dialog below the page...
        modal.innerHTML = ''
    } else {
        modal = document.createElement('dialog')
        modal.id = 'livewire-error'
        modal.style.margin = '50px'
        modal.style.width = 'calc(100% - 100px)'
        modal.style.height = 'calc(100% - 100px)'
        modal.style.borderRadius = '5px'
        modal.style.padding = '0px'
        // Background color is set on the ::backdrop in Livewire's styles...

        // Close on click...
        modal.addEventListener('click', () => hideHtmlModal(modal))

        // Clean up on dialog close. This ensures that the modal dialog captures the escape
        // event first, so that dialog elements below it do not capture the event, before
        // we clean up and remove the modal from the DOM...
        modal.addEventListener('close', () => cleanupModal(modal))

        document.body.prepend(modal)
    }

    let iframe = document.createElement('iframe')
    iframe.style.backgroundColor = '#17161A'
    iframe.style.borderRadius = '5px'
    iframe.style.width = '100%'
    iframe.style.height = '100%'
    modal.appendChild(iframe)

    document.body.style.overflow = 'hidden'
    iframe.contentWindow.document.open()
    iframe.contentWindow.document.write(page.outerHTML)
    iframe.contentWindow.document.close()

    if (modal.open) return

    // Show the modal and focus it to ensure that the escape key works, otherwise it'll
    // be captured by the iframe, then blur so the modal focus ring isn't visible...
    modal.showModal()
    modal.focus()
    modal.blur()
}

// We don't want to clean up in here anymore because we're using the close 
// event to trigger the cleanup. This function is kept here to maintain 
// backwards compatibility as it is an exported function...
export function hideHtmlModal(modal) {
    modal.close()
}

function cleanupModal(modal) {
    modal.outerHTML = ''
    document.body.style.overflow = 'visible'
}
