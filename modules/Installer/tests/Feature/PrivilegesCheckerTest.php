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

use Illuminate\Database\Connection;
use Mockery;
use Modules\Installer\DatabaseTest;
use Modules\Installer\PrivilegeNotGrantedException;
use Modules\Installer\PrivilegesChecker;
use Tests\TestCase;

class PrivilegesCheckerTest extends TestCase
{
    protected array $testerMethods;

    protected function setUp(): void
    {
        $this->testerMethods = PrivilegesChecker::getTesterMethods();

        parent::setUp();
    }

    public function test_it_passes_privilege_checks(): void
    {
        $connection = $this->partialMock(Connection::class);
        $databaseTestMock = Mockery::mock(DatabaseTest::class, [$connection])->makePartial();

        foreach ($this->testerMethods as $method) {
            $databaseTestMock->shouldReceive($method)
                ->once()
                ->andReturn(true);
        }

        $privilegesChecker = new PrivilegesChecker($databaseTestMock);

        $this->expectNotToPerformAssertions();

        $privilegesChecker->check(); // No exception means it passes
    }

    public function test_it_fails_on_any_missing_privileges(): void
    {
        $this->expectException(PrivilegeNotGrantedException::class);

        $connection = $this->partialMock(Connection::class);
        $databaseTestMock = Mockery::mock(DatabaseTest::class, [$connection])->makePartial();

        foreach ($this->testerMethods as $method) {
            $databaseTestMock->shouldReceive($method)
                ->once()
                ->andThrow(new PrivilegeNotGrantedException('12345 SELECT command denied'));

            $privilegesChecker = new PrivilegesChecker($databaseTestMock);

            $privilegesChecker->check();
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
