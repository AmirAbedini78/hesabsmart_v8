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

namespace Modules\Core\Macros;

use Akaunting\Money\Currency;
use Akaunting\Money\Money;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Modules\Core\Application;
use Modules\Core\Facades\Module as ModuleFacade;
use Nwidart\Modules\Module;

trait RegistersMacros
{
    /**
     * Register application core macros.
     */
    public function registerMacros(): void
    {
        $this->registerCarbonMacros();
        $this->registerRouteMacros();
        $this->registerStrMacros();
        $this->registerSchedulerMacros();
        $this->registerRuleMacros();
        $this->registerRequestMacros();
        $this->registerTestResponseMacros();
        $this->registerFilesystemMacros();
        $this->registerModuleMacros();
        $this->registerCurrencyMacros();
        $this->registerEloquentBuilderMacros();
        $this->registerSchemaBuilderMacros();
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('moduleApi', function (string $module, string $path) {
            Route::prefix(Application::API_PREFIX)
                ->middleware('api')
                ->group(module_path($module, $path));
        });

        Route::macro('moduleWeb', function (string $module, string $path) {
            Route::middleware('web')
                ->group(module_path($module, $path));
        });
    }

    protected function registerCarbonMacros(): void
    {
        Carbon::mixin(InteractsWithDates::class);
        CarbonImmutable::mixin(InteractsWithDates::class);
    }

    protected function registerStrMacros(): void
    {
        Str::macro('isBase64Encoded', new IsBase64Encoded);
        Str::macro('clickable', new ClickableUrls);
    }

    protected function registerRuleMacros(): void
    {
        Rule::macro('requiredIfMethodPost', fn (Request $request) => Rule::requiredIf($request->isMethod('POST')));
        Rule::macro('requiredIfMethodPut', fn (Request $request) => Rule::requiredIf($request->isMethod('PUT')));
    }

    protected function registerRequestMacros(): void
    {
        Request::macro('isZapier', fn () => $this->header('user-agent') === 'Zapier');
        Request::macro('getWith', fn () => Str::of($this->get('with', ''))->explode(';')->filter()->all());
        Request::macro('isApi', fn () => $this->is(\Modules\Core\Application::API_PREFIX.'/*'));
        Request::macro('perPage', fn (?int $default = null) => $this->integer('per_page') ?: $default);
    }

    protected function registerTestResponseMacros(): void
    {
        TestResponse::macro('assertActionUnauthorized', fn () => $this->assertJson(['error' => __('users::user.not_authorized')]));
        TestResponse::macro('assertActionOk', fn () => $this->assertJsonMissingExact(['error' => __('users::user.not_authorized')]));
        TestResponse::macro('assertStatusConflict', fn () => $this->assertStatus(Response::HTTP_CONFLICT));
    }

    protected function registerFilesystemMacros(): void
    {
        Filesystem::macro('deepCleanDirectory', new DeepCleanDirectory);
    }

    protected function registerModuleMacros(): void
    {
        ModuleFacade::macro('core', function () {
            return $this->toCollection()->filter(
                fn (Module $module) => in_array($module->getName(), \DetachedHelper::CORE_MODULES)
            )->all();
        });

        ModuleFacade::macro('isCore', function (string $module) {
            return $this->find($module)->isCore();
        });
    }

    protected function registerCurrencyMacros(): void
    {
        Currency::macro('toMoney', function (string|int|float $value, bool $convert = true) {
            /** @var \Akaunting\Money\Currency */
            $currency = $this;

            return new Money(! is_float($value) ? (float) $value : $value, $currency, $convert);
        });
    }

    protected function registerEloquentBuilderMacros(): void
    {
        EloquentBuilder::mixin(new QueryDateRange);
        EloquentBuilder::mixin(new QueryCriteria);
    }

    protected function registerSchemaBuilderMacros(): void
    {
        SchemaBuilder::macro('getIndexesForColumn', function (string $table, string $column) {
            return collect(Schema::getIndexes($table))
                ->filter(fn (array $index) => in_array($column, $index['columns']))
                ->values()
                ->all();
        });

        SchemaBuilder::macro('getForeignKeysForColumn', function (string $table, string $column) {
            return collect(Schema::getForeignKeys($table))
                ->filter(fn (array $index) => in_array($column, $index['columns']))
                ->values()
                ->all();
        });
    }

    protected function registerSchedulerMacros(): void
    {
        Schedule::macro('safeCommand', function ($command, array $parameters = []) {
            /** @var \Illuminate\Console\Scheduling\Schedule */
            $scheduler = $this;

            $canRunProcess = function_exists('proc_get_status') &&
            function_exists('proc_terminate') &&
            function_exists('proc_open') &&
            function_exists('proc_close');

            // https://laracasts.com/discuss/channels/laravel/how-to-call-artisan-commands-programatically-with-valueless-options?page=1&replyId=915122
            // convert ['--param'] to ['--param' => true] or opposite when can't run process.

            foreach ($parameters as $key => $value) {
                if (! $canRunProcess) {
                    if (is_int($key)) {
                        unset($parameters[$key]);
                        $parameters[$value] = true;
                    }
                } elseif (! is_int($key) && is_bool($value)) {
                    unset($parameters[$key]);
                    $parameters[] = $key;
                }
            }

            if ($canRunProcess) {
                return $scheduler->command($command, $parameters);
            }

            return $scheduler->call(function () use ($command, $parameters) {
                Artisan::call($command, $parameters);
            });
        });
    }
}
