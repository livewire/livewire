<?php
return [
    /*
     * If set, the site will be exported to this disk. Disks can be configured
     * in `config/filesystems.php`.
     *
     * If empty, your site will be exported to a `dist` folder.
     */
    'disk' => null,
    /*
     * The entry points of your app. The export crawler will start to build
     * pages from these URL's.
     */
    'entries' => [
        '/docs/quickstart',
    ],
    /*
     * Files that should be included in the build.
     */
    'include' => [
        ['source' => 'public', 'target' => ''],
    ],
    /*
     * Patterns that should be excluded from the build.
     */
    'exclude' => [
        '/\.php$/',
    ],
    /*
     * Shell commands that should be run before the export will be created.
     */
    'before' => [
        // 'assets' => '/usr/local/bin/yarn production',
    ],
    /*
     * Shell commands that should be run after the export was created.
     */
    'after' => [
        // 'deploy' => '/usr/local/bin/netlify deploy --prod',
    ],
];
