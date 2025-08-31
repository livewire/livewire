let fs = require('fs')
let brotliSize = require('brotli-size')
let crypto = require('crypto')
let path = require('path')

// Plugin to replace 'alpinejs' with '@alpinejs/csp' for CSP builds
const alpineCSPPlugin = {
    name: 'alpine-csp',
    setup(build) {
        build.onResolve({ filter: /^alpinejs$/ }, args => {
            return { path: require.resolve('@alpinejs/csp') }
        })
    }
}

build({
    entryPoints: [`js/index.js`],
    outfile: `dist/livewire.js`,
    bundle: true,
    platform: 'browser',
    define: { CDN: true, IS_CSP_BUILD: false },
})

build({
    entryPoints: [`js/index.js`],
    outfile: `dist/livewire.csp.js`,
    bundle: true,
    platform: 'browser',
    define: { CDN: true, IS_CSP_BUILD: true },
    plugins: [alpineCSPPlugin]
})

build({
    format: 'esm',
    entryPoints: [`js/index.js`],
    outfile: `dist/livewire.esm.js`,
    sourcemap: 'linked',
    bundle: true,
    platform: 'node',
    define: { CDN: true, IS_CSP_BUILD: false },
})

build({
    format: 'esm',
    entryPoints: [`js/index.js`],
    outfile: `dist/livewire.csp.esm.js`,
    sourcemap: 'linked',
    bundle: true,
    platform: 'node',
    define: { CDN: true, IS_CSP_BUILD: true },
    plugins: [alpineCSPPlugin]
})


let hash = crypto.randomBytes(4).toString('hex');

fs.writeFileSync(__dirname+'/../dist/manifest.json', `
{"/livewire.js":"${hash}"}
`)

// Build a minified version.
build({
    entryPoints: [`js/index.js`],
    outfile: `dist/livewire.min.js`,
    sourcemap: 'linked',
    bundle: true,
    minify: true,
    platform: 'browser',
    define: { CDN: true, IS_CSP_BUILD: false },
}).then(() => {
    outputSize(`dist/livewire.min.js`)
})

// Build a minified version.
build({
    entryPoints: [`js/index.js`],
    outfile: `dist/livewire.csp.min.js`,
    sourcemap: 'linked',
    bundle: true,
    minify: true,
    platform: 'browser',
    define: { CDN: true, IS_CSP_BUILD: true },
    plugins: [alpineCSPPlugin]
}).then(() => {
    outputSize(`dist/livewire.csp.min.js`)
})

function build(options) {
    options.define || (options.define = {})

    // options.define['LIVEWIRE_VERSION'] = `'${getFromPackageDotJson('alpinejs', 'version')}'`
    options.define['process.env.NODE_ENV'] = process.argv.includes('--watch') ? `'production'` : `'development'`

    return require('esbuild').build({
        watch: process.argv.includes('--watch'),
        // external: ['alpinejs'],
        ...options,
    }).catch(() => process.exit(1))
}

function outputSize(file) {
    let size = bytesToSize(brotliSize.sync(fs.readFileSync(file)))

    console.log("\x1b[32m", `Bundle size [${file}]: ${size}`)
}

function bytesToSize(bytes) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
    if (bytes === 0) return 'n/a'
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
    if (i === 0) return `${bytes} ${sizes[i]}`
    return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`
}
