let mix = require('laravel-mix');

mix.js('src/js/index.js', 'dist/livewire.js').sourceMaps();

mix.webpackConfig({
    output: {
        libraryTarget: 'umd',
    }
})
