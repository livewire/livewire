let mix = require('laravel-mix');

// Allow both "livewire.js" && "livewire.min.js"
// to both exist in the mix.manifest file.
require('laravel-mix-merge-manifest');

const outputFileName = process.env.NODE_ENV === 'production'
    ? 'livewire.min.js'
    : 'livewire.js'

mix
    .js('src/js/index.js', outputFileName)
    .setPublicPath('dist')
    .version()
    .mergeManifest();

mix.webpackConfig({
    output: {
        libraryTarget: 'umd',
    },
    resolve: {
        alias: {
            '@': __dirname + '/src/js'
        }
    }
});
