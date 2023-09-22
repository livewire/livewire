
if (! window._lw_dusk_asset_count) {
    window._lw_dusk_asset_count = 1
} else {
    window._lw_dusk_asset_count++
}

document.addEventListener('alpine:init', () => {
    Alpine.navigate.onNavigate(() => {
        if (! window._lw_dusk_on_navigate) {
            window._lw_dusk_on_navigate = 1
        } else {
            window._lw_dusk_on_navigate++
        }
    })
})
