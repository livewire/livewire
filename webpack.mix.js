let mix = require('laravel-mix');

mix
    .js('src/js/index.js', 'livewire.js')
    .setPublicPath('dist')
    .version();

mix.webpackConfig({
    output: {
        libraryTarget: 'umd',
    }
});

mix.extend('aliasConfig', new class {
    webpackConfig(webpackConfig) {
        webpackConfig.resolve.extensions.push('.js', '.json', '.vue');
        webpackConfig.resolve.alias = {
            '@': __dirname + '/src/js',
        };
    }
});
mix.aliasConfig();
