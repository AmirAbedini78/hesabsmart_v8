<?php

use App\Foundation\CustomApplication;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

require_once __DIR__.'/../detached.php';

return CustomApplication::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Temporary code for Laravel v11 migration.
        if (is_dir(app_path('Exceptions'))) {
            (new \Modules\Core\Macros\DeepCleanDirectory)->__invoke(base_path('bootstrap/cache'));
        }

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(function (Request $request) {
            return $request->user()->landingPage();
        });

        $middleware->append([
            \Modules\Users\Http\Middleware\UpdateUserLastActiveDate::class,
            \Modules\Core\Http\Middleware\AddVersionHeaderToResponse::class,
        ]);

        $middleware->web(append: [
            \Modules\Users\Http\Middleware\Localizeable::class,
            \Modules\Core\Http\Middleware\BlocksBadVisitors::class,
            \Modules\Updater\Http\Middleware\PreventRequestsWhenMigrationNeeded::class,
            \Modules\Updater\Http\Middleware\PreventRequestsWhenUpdateNotFinished::class,
        ]);

        $middleware->api(append: [
            \Modules\Users\Http\Middleware\Localizeable::class,
            \Modules\Core\Http\Middleware\BlocksBadVisitors::class,
            \Modules\Users\Http\Middleware\EnsureApiRequestsAreAllowed::class,
        ]);

        $middleware->statefulApi();

        $middleware->throttleApi();

        $middleware->trimStrings(except: ['database_password']); // installation

        $middleware->preventRequestsDuringMaintenance(except: [
            '/api/update',
            '/api/patches',
            '/migrate',
            '/update/finalize',
        ]);

        $middleware->validateCsrfTokens(except: [
            '/webhook/*', // for general usage
            '/install/*',
            '/forms/f/*',
            '/api/voip/*',
            '/api/translation/*/*',
        ]);

        $middleware->alias([
            'admin' => \Modules\Users\Http\Middleware\OnlySuperAdmin::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
