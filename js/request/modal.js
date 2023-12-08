
// This code and concept is all Jonathan Reinink - thanks main!
export function showHtmlModal(html) {
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
    modal.addEventListener('click', () => hideHtmlModal(modal))

    // Close on escape key press.
    modal.setAttribute('tabindex', 0)
    modal.addEventListener('keydown', e => {
        if (e.key === 'Escape') hideHtmlModal(modal)
    })
    modal.focus()
}

export function hideHtmlModal(modal) {
    modal.outerHTML = ''
    document.body.style.overflow = 'visible'
}
