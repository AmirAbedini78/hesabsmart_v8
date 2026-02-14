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

class Environment
{
    /**
     * Additional .env file variables.
     */
    protected array $additional = [];

    /**
     * Initialize new Environment instance.
     */
    public function __construct(
        protected string $name,
        protected string $key,
        protected string $identificationKey,
        protected string $url,
        protected string $dbHost,
        protected string $dbPort,
        protected string $dbName,
        protected string $dbUser,
        protected string $dbPassword,
    ) {}

    /**
     * Add additional variables to the .env file.
     */
    public function with(array $additional): static
    {
        $this->additional = $additional;

        return $this;
    }

    /**
     * Get the application name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the application key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the application identification key.
     */
    public function getIdentificationKey(): string
    {
        return $this->identificationKey;
    }

    /**
     * Get the application url.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get the database hostname.
     */
    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    /**
     * Get the database port.
     */
    public function getDbPort(): string
    {
        return $this->dbPort;
    }

    /**
     * Get the database name.
     */
    public function getDbName(): string
    {
        return $this->dbName;
    }

    /**
     * Get the database user.
     */
    public function getDbUser(): string
    {
        return $this->dbUser;
    }

    /**
     * Get the database password.
     */
    public function getDbPassword(): string
    {
        return $this->dbPassword;
    }

    /**
     * Get additional .env file variables.
     */
    public function getAdditional(): array
    {
        return $this->additional;
    }
}
