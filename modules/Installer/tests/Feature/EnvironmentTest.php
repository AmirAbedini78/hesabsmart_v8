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
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    private Environment $env;

    protected function setUp(): void
    {
        parent::setUp();

        $this->env = new Environment(
            name: 'MyApp',
            key: 'base64:randomkey==',
            identificationKey: 'idKey123',
            url: 'http://myapp.test',
            dbHost: 'localhost',
            dbPort: '3306',
            dbName: 'myapp_db',
            dbUser: 'myapp_user',
            dbPassword: 'secret',
        );
    }

    public function test_get_name(): void
    {
        $this->assertEquals('MyApp', $this->env->getName());
    }

    public function test_get_key(): void
    {
        $this->assertEquals('base64:randomkey==', $this->env->getKey());
    }

    public function test_get_identification_key(): void
    {
        $this->assertEquals('idKey123', $this->env->getIdentificationKey());
    }

    public function test_get_url(): void
    {
        $this->assertEquals('http://myapp.test', $this->env->getUrl());
    }

    public function test_get_db_host(): void
    {
        $this->assertEquals('localhost', $this->env->getDbHost());
    }

    public function test_get_db_port(): void
    {
        $this->assertEquals('3306', $this->env->getDbPort());
    }

    public function test_get_db_name(): void
    {
        $this->assertEquals('myapp_db', $this->env->getDbName());
    }

    public function test_get_db_user(): void
    {
        $this->assertEquals('myapp_user', $this->env->getDbUser());
    }

    public function test_get_db_password(): void
    {
        $this->assertEquals('secret', $this->env->getDbPassword());
    }

    public function test_get_additional(): void
    {
        $additional = [
            'CACHE_DRIVER' => 'file',
            'SESSION_DRIVER' => 'database',
        ];

        // Use the with method to add additional variables
        $this->env = $this->env->with($additional);

        $this->assertEquals($additional, $this->env->getAdditional());
    }
}
