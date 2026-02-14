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

use Modules\Installer\Environment;
use Modules\Installer\EnvironmentManager;
use Tests\TestCase;

class EnvironmentManagerTest extends TestCase
{
    protected EnvironmentManager $manager;

    protected $envFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new EnvironmentManager;
        $this->envFilePath = base_path('.env.test');
    }

    public function test_it_uses_default_env_file_path(): void
    {
        $this->assertSame(app()->environmentFilePath(), $this->manager->getEnvFilePath());
    }

    public function test_it_has_custom_env_file_path(): void
    {
        $manager = new EnvironmentManager($this->envFilePath);

        $this->assertSame($this->envFilePath, $manager->getEnvFilePath());
    }

    public function test_it_writes_to_env_file(): void
    {
        /** @var \Modules\Installer\Environment */
        $envMock = $this->mock(Environment::class, function ($mock) {
            $mock->shouldReceive('getName')->andReturn('TestApp');
            $mock->shouldReceive('getKey')->andReturn('base64:123456');
            $mock->shouldReceive('getIdentificationKey')->andReturn('IdentKey123');
            $mock->shouldReceive('getUrl')->andReturn('http://testapp.com');
            $mock->shouldReceive('getDbHost')->andReturn('localhost');
            $mock->shouldReceive('getDbPort')->andReturn('3306');
            $mock->shouldReceive('getDbName')->andReturn('testapp_db');
            $mock->shouldReceive('getDbUser')->andReturn('root');
            $mock->shouldReceive('getDbPassword')->andReturn('secret');
            $mock->shouldReceive('getAdditional')->andReturn([
                'ADDITIONAL' => 'something',
            ]);
        });

        $manager = new EnvironmentManager($this->envFilePath);

        if ($manager->getEnvFilePath() !== $this->envFilePath || basename($this->envFilePath) === '.env') {
            $this->markTestSkipped(
                'Cannot set custom installer environment path, skipping to prevent writing to the original .env file.'
            );
        }

        $result = $manager->saveEnvFile($envMock);

        $this->assertTrue($result);

        $expectedContents = "# Read more about editing the environment file: https://www.concordcrm.com/docs/config#edit-env-file\n\n".

        "APP_NAME='TestApp'\n".
        "# DO NOT EDIT THE APPLICATION KEY\n".
        "APP_KEY=base64:123456\n".
        "IDENTIFICATION_KEY=IdentKey123\n".
        "APP_URL=http://testapp.com\n".
        "APP_DEBUG=false\n\n".
        "DB_CONNECTION=mysql\n".
        "DB_HOST=localhost\n".
        "DB_PORT=3306\n".
        "DB_DATABASE=testapp_db\n".
        "DB_USERNAME=root\n".
        "DB_PASSWORD='secret'\n\n".
        'MAIL_MAILER=array'."\n".
        'ADDITIONAL=something'."\n";

        $this->assertStringEqualsFile($this->envFilePath, $expectedContents);
    }

    public function test_guess_url_with_http(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = 'index.php';

        $expectedUrl = 'http://example.com';
        $guessedUrl = EnvironmentManager::guessUrl();

        $this->assertEquals($expectedUrl, $guessedUrl);
    }

    public function test_guess_url_with_https(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $expectedUrl = 'https://example.com';
        $guessedUrl = EnvironmentManager::guessUrl();

        $this->assertEquals($expectedUrl, $guessedUrl);
    }

    public function test_guess_url_with_subdirectory(): void
    {
        $this->markTestSkipped();

        unset($_SERVER['HTTPS']); // Simulate HTTP
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/subdir/index.php';

        $expectedUrl = 'http://example.com/subdir';
        $guessedUrl = EnvironmentManager::guessUrl();

        $this->assertEquals($expectedUrl, $guessedUrl);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->envFilePath)) {
            unlink($this->envFilePath);
        }

        parent::tearDown();
    }
}
