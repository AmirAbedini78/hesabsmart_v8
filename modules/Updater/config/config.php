<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'name' => 'Updater',

    /*
    |--------------------------------------------------------------------------
    | Version installed
    |--------------------------------------------------------------------------
    |
    | Application current installed version.
    */

    'version_installed' => Modules\Core\Application::VERSION,

    /*
    |--------------------------------------------------------------------------
    | General configuration for the updater
    |--------------------------------------------------------------------------
    */

    'archive_url' => env('UPDATER_ARCHIVE_URL', 'https://archive.concordcrm.com'),
    'patches_archive_url' => env('PATCHES_ARCHIVE_URL', 'https://archive.concordcrm.com/patches'),
    'purchase_key' => env('PURCHASE_KEY', ''),
    'download_path' => env('UPDATER_DOWNLOAD_PATH', storage_path('updater')),

    /*
    |--------------------------------------------------------------------------
    | Exclude files from update
    |--------------------------------------------------------------------------
    |
    | Specify files which should not be updated and will be skipped during the
    | update process.
    |
    */
    'exclude_files' => [
        'public/.htaccess',
        'public/web.config',
        'public/robots.txt',
        'public/favicon.ico',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude folders from update
    |--------------------------------------------------------------------------
    |
    | Specify folders which should not be updated and will be skipped during the
    | update process.
    |
    */
    'exclude_folders' => [
        '.git',
        '.idea',
        '__MACOSX',
        'node_modules',
        'bootstrap/cache',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions checker configuration
    |--------------------------------------------------------------------------
    |
    | Specify folders which should be excluded from the permissions checker.
    |
    */
    'permissions' => [
        'exclude_folders' => [
            'node_modules',
            'tests/coverage',
            'concord_crm',
            'storage/app',
            'storage/framework',
            'storage/debugbar',
            'storage/logs',
            'public/storage',
            'vendor/concordcrm/hosted',

            // Webmin files
            'fcgi-bin',
            'snap',

            // CloudLinux files
            '.cagefs',

            // Dev files and big folders
            'resources/js',
            'public/static',

            // Old files
            'app/Innoclapps',
            'modules/*/_old',
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Indicates whether to restart the queue when finalizing the update or patch is applied.
    |---------------------------------------------------------------------------
    */

    'restart_queue' => true,

    /*
    |---------------------------------------------------------------------------
    | Indicates whether the patches should be automatically applied.
    |---------------------------------------------------------------------------
    */

    'auto_patch' => env('AUTO_PATCH', false),

    /*
    |---------------------------------------------------------------------------
    | Indicates whether test releases should be shown, development only.
    |---------------------------------------------------------------------------
    */
    'show_test_releases' => env('SHOW_TEST_RELEASES', false),
];
