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

namespace Modules\Core\Tests\Feature\Controller\Api\Module;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Application;
use Tests\TestCase;
use ZipArchive;

class UploadModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem;

        $filesystem->deleteDirectory(base_path('modules/ValidModule1'));
        $filesystem->deleteDirectory(base_path('modules/ValidModule2'));
        $filesystem->deleteDirectory(base_path('modules/InvalidModule'));
        $filesystem->deleteDirectory(base_path('modules/TestModule'));

        parent::tearDown();
    }

    public function test_upload_valid_module_zip(): void
    {
        $this->signIn();

        $zipFile = $this->createZipArchive(function (ZipArchive $zip) {
            $zip->addFromString('ValidModule1/module.json', json_encode([
                'name' => 'ValidModule1',
                'requires_at_least' => Application::VERSION,
            ]));
        });

        $uploadedFile = new UploadedFile($zipFile, 'modules.zip', 'application/zip', null, true);

        $response = $this->postJson('/api/modules', [
            'file' => $uploadedFile,
        ]);

        $response->assertOk()->assertJson([
            'ValidModule1' => ['success' => true],
        ]);
    }

    public function test_upload_invalid_file_type(): void
    {
        $this->signIn();

        // Prepare a non-zip file
        $uploadedFile = UploadedFile::fake()->create('modules.txt', 100, 'text/plain');

        $response = $this->postJson('/api/modules', [
            'file' => $uploadedFile,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    }

    public function test_upload_zip_without_module_json(): void
    {
        $this->signIn();

        // Prepare a zip file without module.json
        $zipFile = $this->createZipArchive(function (ZipArchive $zip) {
            $zip->addFromString('somefile.txt', 'This is a test file');
        });

        $uploadedFile = new UploadedFile($zipFile, 'modules.zip', 'application/zip', null, true);

        $response = $this->postJson('/api/modules', [
            'file' => $uploadedFile,
        ]);

        $response->assertStatus(409)->assertJson([
            'message' => 'No valid modules found in file.',
        ]);
    }

    public function test_upload_module_not_in_folder()
    {
        $this->signIn();

        // Prepare a module zip file without its own folder
        $zipFile = $this->createZipArchive(function (ZipArchive $zip) {
            $zip->addFromString('module.json', json_encode([
                'name' => 'InvalidModule',
                'requires_at_least' => '1.0.0',
            ]));
        });

        $uploadedFile = new UploadedFile($zipFile, 'modules.zip', 'application/zip', null, true);

        $response = $this->postJson('/api/modules', [
            'file' => $uploadedFile,
        ]);

        $response->assertOk()->assertJson([
            'InvalidModule' => [
                'success' => false,
                'errorReason' => 'The contents of the module(s) should be placed within its own folder, named after the module itself.',
            ],
        ]);
    }

    public function test_upload_module_version_requirement_not_met(): void
    {
        $this->signIn();

        // Prepare a module zip file with a version requirement not met
        $zipFile = $this->createZipArchive(function (ZipArchive $zip) {
            $zip->addFromString('TestModule/module.json', json_encode([
                'name' => 'TestModule',
                'requires_at_least' => '500.0.0',
            ]));
        });

        $uploadedFile = new UploadedFile($zipFile, 'modules.zip', 'application/zip', null, true);

        $response = $this->postJson('/api/modules', [
            'file' => $uploadedFile,
        ]);

        $response->assertOk()->assertJson([
            'TestModule' => [
                'success' => false,
                'errorReason' => 'This module requires at least version 500.0.0.',
            ],
        ]);
    }

    public function test_upload_multiple_modules_in_archive()
    {
        $this->signIn();

        // Prepare a zip file with multiple modules
        $zipFile = $this->createZipArchive(function (ZipArchive $zip) {
            // First valid module
            $zip->addFromString('ValidModule1/module.json', json_encode([
                'name' => 'ValidModule1',
                'requires_at_least' => '1.0.0',
            ]));

            // Second valid module
            $zip->addFromString('ValidModule2/module.json', json_encode([
                'name' => 'ValidModule2',
                'requires_at_least' => '1.0.0',
            ]));

            // Invalid module (not in its own folder)
            $zip->addFromString('module.json', json_encode([
                'name' => 'InvalidModule',
                'requires_at_least' => '1.0.0',
            ]));

            // Module with version requirement not met
            $zip->addFromString('TestModule/module.json', json_encode([
                'name' => 'TestModule',
                'requires_at_least' => '10.0.0',
            ]));
        });

        $uploadedFile = new UploadedFile($zipFile, 'modules.zip', 'application/zip', null, true);

        $response = $this->postJson('/api/modules', [
            'file' => $uploadedFile,
        ]);

        $response->assertOk()->assertJson([
            'ValidModule1' => ['success' => true],
            'ValidModule2' => ['success' => true],
            'InvalidModule' => [
                'success' => false,
                'errorReason' => 'The contents of the module(s) should be placed within its own folder, named after the module itself.',
            ],
            'TestModule' => [
                'success' => false,
                'errorReason' => 'This module requires at least version 10.0.0.',
            ],
        ]);
    }

    protected function createZipArchive($callback): string
    {
        $zip = new ZipArchive;
        $zipFile = tempnam(sys_get_temp_dir(), 'zip');
        $zip->open($zipFile, ZipArchive::CREATE);

        $callback($zip);

        $zip->close();

        return $zipFile;
    }
}
