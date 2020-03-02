module.exports = {
    presets: [
        [
            '@babel/preset-env',
            {
                targets: {
                    node: 'current',
                    edge: '18',
                    ie: "11",
                },
            },
        ],
    ],
    plugins: [
        "@babel/plugin-proposal-object-rest-spread",
    ],
    env: {
        test: {
            presets: [
                [
                    '@babel/preset-env',
                    {
                        targets: {
                            node: 'current',
                        },
                    }
                ]
            ]
        }
    }
};
