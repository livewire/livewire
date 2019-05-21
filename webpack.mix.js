let mix = require('laravel-mix');

mix
    .js('src/js/index.js', 'livewire.js')
    .setPublicPath('dist')
    .version();

mix.webpackConfig({
    output: {
        libraryTarget: 'umd',
    }
})
