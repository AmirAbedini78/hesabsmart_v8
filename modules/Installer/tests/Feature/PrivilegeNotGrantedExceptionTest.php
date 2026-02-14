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

namespace Modules\Installer\Tests\Feature;

use Modules\Installer\PrivilegeNotGrantedException;
use Tests\TestCase;

class PrivilegeNotGrantedExceptionTest extends TestCase
{
    public function test_it_correctly_extracts_privilege_name_from_message(): void
    {
        $message = '12345 SELECT command denied';
        $exception = new PrivilegeNotGrantedException($message);

        $this->assertEquals('SELECT', $exception->getPriviligeName(), 'The privilege name should be extracted correctly from the exception message');
    }
}
