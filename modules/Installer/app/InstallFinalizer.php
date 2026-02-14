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

namespace Modules\Installer;

use Exception;
use Modules\Core\Environment;
use Modules\Core\Facades\Innoclapps;
use Modules\Installer\Events\InstallationSucceeded;
use Modules\Installer\Exceptions\FailedToFinalizeInstallationException;

class InstallFinalizer
{
    /**
     * Finalize the installation.
     *
     * @throws FailedToFinalizeInstallationException
     */
    public function handle($currency, $country): void
    {
        $event = new InstallationSucceeded([]);

        $installer = app(Installer::class);

        $this->createStorageLink($event);

        if ($this->markAsInstalled($event, $installer)) {
            settings(['currency' => $currency, 'company_country_id' => $country]);

            event($event);

            Environment::setInstallationDate();
            Environment::capture();
        }

        $this->failWithErrorMessageIfFailing($event);

        Innoclapps::optimize();
    }

    /**
     * Mark the installation as installed.
     */
    protected function markAsInstalled(InstallationSucceeded $event, Installer $installer): bool
    {
        $success = $installer->markAsInstalled();

        if (! $success) {
            $event->addError(
                sprintf('Failed to create the installed file. (%s).', $installer::installedFileLocation())
            );
        }

        return $success;
    }

    /**
     * Display any errors occured during the installation.
     *
     * @throws FailedToFinalizeInstallationException
     */
    protected function failWithErrorMessageIfFailing(InstallationSucceeded $event): void
    {
        if ($event->hasErrors()) {
            throw new FailedToFinalizeInstallationException(
                implode(PHP_EOL, $event->getErrors())
            );
        }
    }

    /**
     * Create the storage link.
     */
    protected function createStorageLink(InstallationSucceeded $event): void
    {
        try {
            Innoclapps::createStorageLink();
        } catch (Exception) {
            $event->addError('Failed to create storage symlink.');
        }
    }
}
