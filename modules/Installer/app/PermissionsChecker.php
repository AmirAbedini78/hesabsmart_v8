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

class PermissionsChecker
{
    protected array $folders;

    protected array $results = [
        'results' => [],
    ];

    /**
     * Initialize new PermissionsChecker instance.
     */
    public function __construct(?array $folders = null)
    {
        $this->folders = $folders ?? config('installer.permissions');
    }

    /**
     * Check for the folders permissions.
     */
    public function check(): array
    {
        foreach ($this->folders as $folder => $permission) {
            if (! ($this->getPermission($folder) >= $permission)) {
                $this->addFileAndSetErrors($folder, $permission, false);
            } else {
                $this->addFile($folder, $permission, true);
            }
        }

        return $this->results;
    }

    /**
     * Get a folder permission.
     */
    public function getPermission(string $folder): string
    {
        return substr(sprintf('%o', fileperms(base_path($folder))), -4);
    }

    /**
     * Add the file to the list of results.
     */
    private function addFile(string $folder, string $permission, bool $isSet): void
    {
        $this->results['results'][] = [
            'folder' => $folder,
            'permission' => $permission,
            'isSet' => $isSet,
        ];
    }

    /**
     * Add the file and set the errors.
     */
    private function addFileAndSetErrors(string $folder, string $permission, bool $isSet): void
    {
        $this->addFile($folder, $permission, $isSet);

        $this->results['errors'] = true;
    }
}
