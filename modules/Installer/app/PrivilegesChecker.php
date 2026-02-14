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

class PrivilegesChecker
{
    /**
     * Initialize new PrivilegesChecker instance.
     */
    public function __construct(protected DatabaseTest $tester) {}

    /**
     * Check the privileges.
     *
     * @throws \Modules\Installer\PrivilegeNotGrantedException
     */
    public function check(): void
    {
        $testMethods = $this->getTesterMethods();

        foreach ($testMethods as $test) {
            $this->tester->{$test}();

            throw_if(
                $this->tester->getLastError(),
                new PrivilegeNotGrantedException($this->tester->getLastError())
            );
        }
    }

    /**
     * Get the tester methods.
     */
    public static function getTesterMethods(): array
    {
        return [
            'testDropTable', // Should be first, it's the most important because all other tests are dropping the table.
            'testCreateTable',
            'testSelect',
            'testInsert',
            'testUpdate',
            'testDelete',
            'testAlter',
            'testIndex',
            'testReferences',
        ];
    }
}
