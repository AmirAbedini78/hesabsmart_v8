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

namespace Modules\Core\Tests\Unit;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Mockery;
use Modules\Core\Module\ModuleUploader;
use Modules\Core\Module\ModuleUploadException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;
use ZipArchive;

class ModuleUploaderTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_upload_with_no_module_json_file()
    {
        $this->expectException(ModuleUploadException::class);
        $this->expectExceptionMessage('No valid modules found in file.');

        $filesystem = Mockery::mock(Filesystem::class);
        $uploadedFile = UploadedFile::fake()->create('modules.zip');

        $zip = Mockery::mock(ZipArchive::class);
        $zip->shouldReceive('open')->andReturn(true);
        $zip->shouldReceive('extractTo')->andReturn(true);
        $zip->shouldReceive('close')->once();

        $finder = Mockery::mock(Finder::class);
        $finder->shouldReceive('in')->andReturn($finder);
        $finder->shouldReceive('files')->andReturn($finder);
        $finder->shouldReceive('name')->andReturn($finder);
        $finder->shouldReceive('count')->andReturn(0);

        $uploader = Mockery::mock(ModuleUploader::class, [$uploadedFile])->makePartial();
        $uploader->shouldAllowMockingProtectedMethods();
        $uploader->shouldReceive('createTempDirectory')->andReturn('tempDir');
        $uploader->shouldReceive('createUniqueTempDirectory')->andReturn('tempDir/unique');
        $uploader->shouldReceive('getFilesystem')->andReturn($filesystem);

        // Use a partial mock for the ZipArchive to ensure close is called
        $uploader->shouldReceive('extractZipTo')->withArgs(function ($destination) use ($zip, $uploadedFile) {
            if (true !== ($zip->open($uploadedFile->getRealPath()))) {
                throw new ModuleUploadException('Cannot open zip archive.', 409);
            }

            if (! $zip->extractTo($destination)) {
                throw new ModuleUploadException('Failed to extract module.', 409);
            }

            $zip->close();

            return true;
        });

        $uploader->shouldReceive('findModuleJsonFiles')->andReturn($finder);

        $uploader->upload();
    }

    public function test_upload_with_valid_module_json_file(): void
    {
        $moduleJsonContent = json_encode([
            'name' => 'TestModule',
            'requires_at_least' => '1.5.0',
        ]);

        $filesystem = Mockery::mock(Filesystem::class);
        $uploadedFile = UploadedFile::fake()->create('modules.zip');

        $zip = Mockery::mock(ZipArchive::class);
        $zip->shouldReceive('open')->andReturn(true);
        $zip->shouldReceive('extractTo')->andReturn(true);
        $zip->shouldReceive('close')->once();

        $finder = Mockery::mock(Finder::class);
        $finder->shouldReceive('in')->andReturn($finder);
        $finder->shouldReceive('files')->andReturn($finder);
        $finder->shouldReceive('name')->andReturn($finder);
        $finder->shouldReceive('count')->andReturn(1);
        $finder->shouldReceive('getIterator')->andReturn(new \ArrayIterator([
            Mockery::mock(SplFileInfo::class, [
                'getRelativePath' => 'TestModule',
                'getContents' => $moduleJsonContent,
            ]),
        ]));

        $uploader = Mockery::mock(ModuleUploader::class, [$uploadedFile])->makePartial();
        $uploader->shouldAllowMockingProtectedMethods();
        $uploader->shouldReceive('createTempDirectory')->andReturn('tempDir');
        $uploader->shouldReceive('createUniqueTempDirectory')->andReturn('tempDir/unique');
        $uploader->shouldReceive('getFilesystem')->andReturn($filesystem);

        // Use a partial mock for the ZipArchive to ensure close is called
        $uploader->shouldReceive('extractZipTo')->withArgs(function ($destination) use ($zip, $uploadedFile) {
            if (true !== ($zip->open($uploadedFile->getRealPath()))) {
                throw new Exception('Cannot open zip archive.', 409);
            }

            if (! $zip->extractTo($destination)) {
                throw new Exception('Failed to extract module.', 409);
            }

            $zip->close();

            return true;
        });

        $uploader->shouldReceive('findModuleJsonFiles')->andReturn($finder);
        $uploader->shouldReceive('processModules')->andReturn(['TestModule' => ['success' => true]]);

        $result = $uploader->upload();

        $this->assertEquals(['TestModule' => ['success' => true]], $result);
    }

    public function test_upload_with_module_version_requirement_not_met(): void
    {
        $moduleJsonContent = json_encode([
            'name' => 'TestModule',
            'requires_at_least' => '10.0.0',
        ]);

        $filesystem = Mockery::mock(Filesystem::class);
        $uploadedFile = UploadedFile::fake()->create('modules.zip');

        $zip = Mockery::mock(ZipArchive::class);
        $zip->shouldReceive('open')->andReturn(true);
        $zip->shouldReceive('extractTo')->andReturn(true);
        $zip->shouldReceive('close')->once();

        $finder = Mockery::mock(Finder::class);
        $finder->shouldReceive('in')->andReturn($finder);
        $finder->shouldReceive('files')->andReturn($finder);
        $finder->shouldReceive('name')->andReturn($finder);
        $finder->shouldReceive('count')->andReturn(1);
        $finder->shouldReceive('getIterator')->andReturn(new \ArrayIterator([
            Mockery::mock(SplFileInfo::class, [
                'getRelativePath' => 'TestModule',
                'getContents' => $moduleJsonContent,
            ]),
        ]));

        $uploader = Mockery::mock(ModuleUploader::class, [$uploadedFile])->makePartial();
        $uploader->shouldAllowMockingProtectedMethods();
        $uploader->shouldReceive('createTempDirectory')->andReturn('tempDir');
        $uploader->shouldReceive('createUniqueTempDirectory')->andReturn('tempDir/unique');
        $uploader->shouldReceive('getFilesystem')->andReturn($filesystem);

        // Use a partial mock for the ZipArchive to ensure close is called
        $uploader->shouldReceive('extractZipTo')->withArgs(function ($destination) use ($zip, $uploadedFile) {
            if (true !== ($zip->open($uploadedFile->getRealPath()))) {
                throw new Exception('Cannot open zip archive.', 409);
            }

            if (! $zip->extractTo($destination)) {
                throw new Exception('Failed to extract module.', 409);
            }

            $zip->close();

            return true;
        });

        $uploader->shouldReceive('findModuleJsonFiles')->andReturn($finder);
        $uploader->shouldReceive('isMinimumVersionMet')->andReturn(false);

        $result = $uploader->upload();

        $this->assertArrayHasKey('TestModule', $result);
        $this->assertFalse($result['TestModule']['success']);
        $this->assertEquals('This module requires at least version 10.0.0.', $result['TestModule']['errorReason']);
    }
}
