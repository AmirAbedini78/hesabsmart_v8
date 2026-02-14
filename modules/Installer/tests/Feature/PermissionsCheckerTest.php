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

use Illuminate\Support\Facades\File;
use Modules\Installer\PermissionsChecker;
use Tests\TestCase;

class PermissionsCheckerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = storage_path('framework/testing/temp_permissions_checker');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->tempDir);

        parent::tearDown();
    }

    public function test_it_passes_when_permissions_are_correct(): void
    {
        File::makeDirectory($this->tempDir, 0755, true, true);

        $checker = new PermissionsChecker([
            'storage/framework/testing/temp_permissions_checker' => '0755',
        ]);

        $results = $checker->check();

        $this->assertFalse($results['errors'] ?? false);
        $this->assertNotEmpty($results['results']);
        $this->assertTrue($results['results'][0]['isSet']);
    }

    public function test_it_fails_when_permissions_are_incorrect(): void
    {
        File::makeDirectory($this->tempDir, 0755, true, true);

        $checker = $this->getMockBuilder(PermissionsChecker::class)
            ->setConstructorArgs([
                [$expectedPath = 'storage/framework/testing/temp_permissions_checker' => '0777'],
            ])
            ->onlyMethods(['getPermission'])
            ->getMock();

        $checker->expects($this->once())
            ->method('getPermission')
            ->with($expectedPath)
            ->willReturn('0755');

        $results = $checker->check();

        $this->assertTrue($results['errors'] ?? false);
        $this->assertNotEmpty($results['results']);
        $this->assertFalse($results['results'][0]['isSet']);
    }

    public function test_it_handles_empty_permissions_array(): void
    {
        $checker = new PermissionsChecker([]);

        $results = $checker->check();

        $this->assertArrayNotHasKey('errors', $results);
        $this->assertEmpty($results['results']);
    }

    public function test_it_uses_default_permissions_from_config(): void
    {
        config(['installer.permissions' => [
            'storage/framework/testing/temp_permissions_checker' => '0755',
        ]]);

        File::makeDirectory($this->tempDir, 0755, true, true);

        $checker = new PermissionsChecker;
        $results = $checker->check();

        $this->assertFalse($results['errors'] ?? false);
        $this->assertNotEmpty($results['results']);
        $this->assertTrue($results['results'][0]['isSet']);
    }
}
