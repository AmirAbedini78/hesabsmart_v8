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

namespace Modules\Core\Module;

use Closure;
use Illuminate\Support\Facades\Event;

class ModuleBootstrapper
{
    protected bool $resetsMigrations = false;

    public function __construct(protected string $module) {}

    public function onDeleteResetMigrations(): static
    {
        $this->resetsMigrations = true;

        return $this;
    }

    public function resetsMigrations(): bool
    {
        return $this->resetsMigrations;
    }

    public function enabling(Closure $listener): static
    {
        $this->registerEvent('enabling', $listener);

        return $this;
    }

    public function enabled(Closure $listener): static
    {
        $this->registerEvent('enabled', $listener);

        return $this;
    }

    public function disabling(Closure $listener): static
    {
        $this->registerEvent('disabling', $listener);

        return $this;
    }

    public function disabled(Closure $listener): static
    {
        $this->registerEvent('disabled', $listener);

        return $this;
    }

    public function deleting(Closure $listener): static
    {
        $this->registerEvent('deleting', $listener);

        return $this;
    }

    public function deleted(Closure $listener): static
    {
        $this->registerEvent('deleted', $listener);

        return $this;
    }

    protected function registerEvent(string $event, Closure $listener): void
    {
        Event::listen(sprintf('modules.%s.%s', $this->module, $event), function () use ($listener) {
            $listener(app());
        });
    }
}
