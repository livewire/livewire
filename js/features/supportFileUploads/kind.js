// Buckets a file into a coarse UI category — 'image', 'audio', 'video', or
// 'file' — so templates can branch (file.isImage) or map (icons[file.kind])
// without hand-rolling extension lists. Named "kind" (à la Finder) rather
// than "type" because the native File.type is already the MIME string...

let extensions = {
    image: ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'avif', 'bmp', 'ico', 'heic'],
    audio: ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'flac', 'aac'],
    video: ['mp4', 'mov', 'webm', 'avi', 'mkv', 'm4v', 'ogv'],
}

// From a real MIME type ("image/png" → 'image') — the most trustworthy
// source, available while the native File object is still around...
export function kindFromMime(mime) {
    let type = (mime ?? '').split('/')[0]

    return type in extensions ? type : 'file'
}

// From a filename's extension — the fallback once all that's left of the
// file is a pointer to it (finished uploads, stored files)...
export function kindFromName(name) {
    let extension = (name ?? '').split('.').pop().toLowerCase()

    return Object.keys(extensions).find(kind => extensions[kind].includes(extension)) ?? 'file'
}
