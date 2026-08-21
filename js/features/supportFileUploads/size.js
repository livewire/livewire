// Format a byte count the way Laravel's Number::fileSize() does — same units,
// same base-1024 divisor, and the same "promote at 0.9" threshold so a size
// never displays as an awkward near-miss like "1010 KB"...
export function sizeForHumans(bytes) {
    let units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']

    let i = 0

    while (bytes / 1024 > 0.9 && i < units.length - 1) {
        bytes /= 1024

        i++
    }

    return `${Math.round(bytes * 10) / 10} ${units[i]}`
}
