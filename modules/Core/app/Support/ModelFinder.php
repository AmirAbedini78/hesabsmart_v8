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
use Modules\Core\Facades\Module;
use Symfony\Component\Finder\Finder;

class ModelFinder
{
    protected static ?array $models = null;

    protected ?array $paths = null;

    public function in(string|array $paths)
    {
        $this->paths = (array) $paths;

        return $this;
    }

    public function find(): array
    {
        if (! is_null(static::$models)) {
            return static::$models;
        }

        return static::$models = collect($this->finder())->map(function ($model) {
            if (str_contains($model, config('modules.paths.modules'))) {
                return config('modules.namespace').'\\'.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::of($model->getRealPath())
                        ->after(realpath(config('modules.paths.modules')).DIRECTORY_SEPARATOR)
                        ->replaceFirst(str_replace('/', DIRECTORY_SEPARATOR, config('modules.paths.app_folder')), '')
                );
            }

            return app()->getNamespace().str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($model->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
            );
        })->all();
    }

    protected function finder(): Finder
    {
        return (new Finder)->in($this->paths())->files()->name('*.php');
    }

    protected function paths(): array
    {
        if (! is_null($this->paths)) {
            return $this->paths;
        }

        $paths = array_filter(array_values(array_map(function ($module) {
            $path = module_path($module->getLowerName(), config('modules.paths.generator.model.path'));

            return is_dir($path) ? $path : null;
        }, Module::allEnabled())));

        $paths[] = app_path('Models');

        return $paths;
    }
}
