module.exports = {
    presets: [
        [
            '@babel/preset-env',
            {
                targets: {
                    node: 'current',
                    ie: '11'
                },
            },
        ],
    ],
};
