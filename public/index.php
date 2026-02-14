<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require_once __DIR__.'/../detached.php';

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/
if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
    if (DetachedHelper::shouldDisableMaintenance()) {
        DetachedHelper::disableMaintenance();
    } else {
        require __DIR__.'/../storage/framework/maintenance.php';
    }
}

/*
|--------------------------------------------------------------------------
| Check PHP Version requirements.
|--------------------------------------------------------------------------
|
| It's important first to check the minimum required PHP version to prevent any
| errors thrown without the user figuring that the issue is related to the
| actual PHP version.
|
*/
if (! DetachedHelper::isUsingMinimumPhpVersion()) {
    exit(sprintf('<h1>At least PHP %s is required to run the application.</h1>', DetachedHelper::MINIMUM_PHP_VERSION));
}

/*
|--------------------------------------------------------------------------
| Early memory limit checks.
|--------------------------------------------------------------------------
|
| We will check the memory limit before the autoloader is included to prevent any
| ghost errors thrown before installation or while using the application
| as in most cases if the memory limit is 32MB, will fail.
|
| The recommended minimum memory limit is 128MB
| as because of the auto updater feature uses most memory during update.
|
*/
if (DetachedHelper::memoryLimitIsTooLow()) {
    echo '<h1>Memory limit too low.</h1>';
    echo '<p>The minimum recommended memory limit is '.DetachedHelper::MINIMUM_RECOMMENDED_PHP_MEMORY_LIMIT.' MB.</p>';
    echo '<p>Increase the PHP <strong>memory_limit</strong> ini value to continue.</p>';
    exit(0);
}

/*
|--------------------------------------------------------------------------
| Early core modules disable checks.
|--------------------------------------------------------------------------
|
| We will check if there are any core modules disabled and show
| a message to the user instructing to enable the modules.
|
| The modules are not "decoupled" from each other, hence, disabling modules is not allowed
| as the application may crash. At this time the modules feature is intended for better code organization.
|
*/
if (DetachedHelper::hasCoreModulesDisabled()) {
    echo '<h1>Core modules disabled.</h1>';
    echo '<p>The modules are not "decoupled" from each other, hence, disabling modules is not allowed.</p>';
    echo '<p>Edit the modules_statuses.json file and enable the <b>'.implode(', ', DetachedHelper::CORE_MODULES).'</b>  modules.</p>';
    exit(0);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Register the Modules autoloader...
(new \Modules\Core\Module\ModuleAutoloader())->register();

/*
|--------------------------------------------------------------------------
| Perform Pre Install Checks
|--------------------------------------------------------------------------
|
| Before installation, the application key won't be set and there will
| be an error when running the install route, in this case, we will
| include the PreInstal file to configure at least the "APP_KEY"
| environment variable so the installation can be performed properly
|
*/
if (\Modules\Installer\Installer::requiresInstallation()) {
    (new \Modules\Installer\PreInstall(
        \Modules\Installer\Installer::BASE_PATH,
        \Modules\Installer\EnvironmentManager::guessUrl()
    ))->init();

    if (! \Modules\Installer\Installer::isInstalling()) {
        exit(header('Location: /'.\Modules\Installer\Installer::ROUTE_PREFIX));
    }
}

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
