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

namespace Modules\Core\Support;

use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class SubClassDiscovery
{
    use Makeable;

    protected array $directories = [];

    protected bool $inModules = false;

    protected string $path;

    protected string $namespace;

    public function __construct(protected string $subclass, string|array|null $directories = null)
    {
        $this->namespace = app()->getNamespace();

        $this->path = app_path();

        if ($directories) {
            $this->in($directories);
        }
    }

    public function moduleable(): static
    {
        $this->namespace = config('modules.namespace').'\\';
        $this->path = config('modules.paths.modules');

        return $this;
    }

    public function in(string|array $directories): static
    {
        $this->directories = (array) $directories;

        return $this;
    }

    public function find(): array
    {
        $classes = [];

        foreach ((new Finder)->in($this->directories)->files() as $class) {
            $class = $this->namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::of($class->getPathname())->replace($this->path.DIRECTORY_SEPARATOR, '')->replaceFirst(
                    str_replace('/', DIRECTORY_SEPARATOR, config('modules.paths.app_folder')), ''
                )
            );

            if (is_subclass_of($class, $this->subclass) && ! (new ReflectionClass($class))->isAbstract()) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
