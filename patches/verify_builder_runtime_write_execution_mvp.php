<?php

declare(strict_types=1);

use App\Models\BuilderDefinition;
use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Services\Builder\BuilderPostBackupRuntimeWriteReadinessService;
use App\Services\Builder\BuilderPublishApprovalService;
use App\Services\Builder\BuilderPublishExecutionPreparationService;
use App\Services\Builder\BuilderPublishStagedFileValidationService;
use App\Services\Builder\BuilderRuntimeWriteBackupArtifactService;
use App\Services\Builder\BuilderRuntimeWriteExecutionPreflightService;
use App\Services\Builder\BuilderRuntimeWriteExecutionService;
use App\Services\Builder\BuilderRuntimeWriteFinalConfirmationService;
use App\Services\Builder\BuilderRuntimeWriteKillSwitchGuardService;
use App\Services\Builder\BuilderRuntimeWriteOperatorAcknowledgementService;
use App\Services\Builder\BuilderRuntimeWritePlanArtifactService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$errors = [];
$cleanupPaths = [];
$cleanupDefinitionIds = [];
$originalRuntimeWriteConfig = config('builder.runtime_write.enabled', false);
$originalMaxFiles = config('builder.runtime_write.max_files_per_execution', 25);
$originalMaxBytes = config('builder.runtime_write.max_total_bytes_per_execution', 5242880);

function fail_if(bool $condition, string $message): void
{
    global $errors;

    if ($condition) {
        $errors[] = $message;
    }
}

function project_path(string $path): string
{
    global $root;

    return $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function read_project_file(string $path): string
{
    $full = project_path($path);

    return is_file($full) ? (string) file_get_contents($full) : '';
}

function json_contract(string $path): array
{
    global $errors;

    $full = project_path($path);
    if (! is_file($full)) {
        $errors[] = "Missing JSON contract: {$path}";

        return [];
    }

    $decoded = json_decode((string) file_get_contents($full), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Invalid JSON contract {$path}: ".json_last_error_msg();

        return [];
    }

    return is_array($decoded) ? $decoded : [];
}

function contains_text(string $haystack, string $needle, string $label): void
{
    fail_if(! str_contains($haystack, $needle), "{$label} missing {$needle}");
}

function temp_definition(string $moduleName): array
{
    return [
        'schemaVersion' => 1,
        'module' => [
            'name' => $moduleName,
            'namespace' => $moduleName,
            'singularLabel' => $moduleName.' Record',
            'pluralLabel' => $moduleName.' Records',
            'table' => Str::snake($moduleName).'_records',
            'routeName' => Str::kebab($moduleName).'-records',
            'resourceName' => Str::kebab($moduleName).'-records',
            'icon' => 'Settings',
        ],
        'resource' => [
            'modelClass' => $moduleName.'Record',
            'titleField' => 'title',
            'orderBy' => 'title',
            'globalSearchAction' => true,
            'hasDetailView' => true,
        ],
        'fields' => [
            ['name' => 'id', 'type' => 'id', 'label' => 'ID', 'primary' => false, 'required' => false],
            ['name' => 'title', 'type' => 'text', 'label' => 'Title', 'primary' => true, 'required' => true],
        ],
        'relations' => [],
        'capabilities' => ['tableable' => true, 'hasDetailView' => true],
        'formLayout' => ['enabled' => false, 'mode' => 'standard', 'sections' => [], 'stepper' => ['enabled' => false, 'steps' => []], 'conditions' => []],
        'automation' => ['enabled' => false, 'workflows' => []],
    ];
}

function create_definition(string $moduleName): BuilderDefinition
{
    global $cleanupDefinitionIds;

    $definition = BuilderDefinition::create([
        'uuid' => (string) Str::uuid(),
        'name' => $moduleName,
        'slug' => Str::kebab($moduleName),
        'module_name' => $moduleName,
        'entity_name' => $moduleName.'Record',
        'resource_name' => Str::kebab($moduleName).'-records',
        'status' => 'validated',
        'schema_version' => 1,
        'definition_json' => temp_definition($moduleName),
        'checksum' => hash('sha256', json_encode(temp_definition($moduleName))),
    ]);

    $cleanupDefinitionIds[] = $definition->getKey();

    return $definition;
}

function seed_staged_file(BuilderPublishExecution $execution, string $moduleName): void
{
    $path = $execution->staging_root.'/backend/'.$moduleName.'Model.php.stub';
    File::ensureDirectoryExists(dirname(project_path($path)));
    File::put(project_path($path), "<?php\n\nnamespace Modules\\{$moduleName}\\App\\Models;\n\nclass {$moduleName}Model\n{\n    public string \$runtimeWriteMvp = 'verified';\n}\n");
}

function run_chain(string $moduleName, bool $runtimeWriteEnabled): array
{
    config([
        'builder.runtime_write.enabled' => $runtimeWriteEnabled,
        'builder.runtime_write.max_files_per_execution' => 25,
        'builder.runtime_write.max_total_bytes_per_execution' => 5242880,
    ]);

    $definition = create_definition($moduleName);
    $approval = app(BuilderPublishApprovalService::class)->requestApproval($definition);
    app(BuilderPublishApprovalService::class)->approve($approval);

    $executionReport = app(BuilderPublishExecutionPreparationService::class)->prepare($definition->fresh());
    $execution = BuilderPublishExecution::findOrFail($executionReport['execution_id']);
    global $cleanupPaths;
    foreach ([
        'storage/app/builder-publish-staging/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-publish-rollbacks/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-publish-staged-validations/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-runtime-write-plans/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-runtime-write-preflights/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-publish-backups/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-runtime-write-readiness/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-runtime-write-guards/'.$definition->getKey().'/'.$execution->getKey(),
        'storage/app/builder-runtime-write-executions/'.$definition->getKey().'/'.$execution->getKey(),
    ] as $path) {
        $cleanupPaths[] = $path;
    }
    seed_staged_file($execution, $moduleName);

    app(BuilderPublishStagedFileValidationService::class)->validate($execution->fresh());
    app(BuilderRuntimeWritePlanArtifactService::class)->plan($execution->fresh());
    $confirmation = app(BuilderRuntimeWriteFinalConfirmationService::class)->request($execution->fresh());
    app(BuilderRuntimeWriteFinalConfirmationService::class)->grant($confirmation['confirmation']);
    app(BuilderRuntimeWriteExecutionPreflightService::class)->preflight($execution->fresh());
    app(BuilderRuntimeWriteBackupArtifactService::class)->prepare($execution->fresh());
    app(BuilderPostBackupRuntimeWriteReadinessService::class)->readiness($execution->fresh());
    app(BuilderRuntimeWriteKillSwitchGuardService::class)->check($execution->fresh());
    $ack = app(BuilderRuntimeWriteOperatorAcknowledgementService::class)->request($execution->fresh());
    app(BuilderRuntimeWriteOperatorAcknowledgementService::class)->acknowledge($ack['acknowledgement']);

    return [$definition->fresh(), $execution->fresh()];
}

foreach ([
    'app/Services/Builder/BuilderRuntimeWriteExecutionService.php',
    'docs/ai/03-architecture/builder-runtime-write-execution-mvp.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-execution-mvp-contract.json',
    'docs/ai/04-docops/history/2026-07-13-builder-runtime-write-execution-mvp.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$service = read_project_file('app/Services/Builder/BuilderRuntimeWriteExecutionService.php');
foreach ([
    "config('builder.runtime_write.enabled'",
    'storage/app/builder-runtime-write-executions',
    'Cache::lock',
    "'publish_executed' => false",
    "'migrations_run' => false",
    "'routes_registered' => false",
    "'module_marked_published' => false",
    "'rollback_executed' => false",
    'modules/Core',
    'modules/SaaS',
    'modules/Updater',
    'modules/Installer',
    'public/build',
    'vendor',
    'node_modules',
    'resources/js/app.js',
    'routes/web.php',
] as $required) {
    contains_text($service, $required, 'runtime write execution service');
}

$controller = read_project_file('app/Http/Controllers/Builder/BuilderPublishExecutionController.php');
contains_text($controller, 'executeRuntimeWrite', 'execution controller');

$routes = read_project_file('routes/api.php');
contains_text($routes, 'execute-runtime-write', 'routes');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime endpoint exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish endpoint exists.');
fail_if(str_contains($routes, 'run-generated-migrations'), 'Forbidden run-generated-migrations endpoint exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback endpoint exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
contains_text($api, 'executeRuntimeWrite', 'builderApi');
foreach (['publishDefinition', 'executePublish', 'rollbackPublish', 'runGeneratedMigrations', 'markModulePublished', 'overrideKillSwitch', 'enableRuntimeWrite'] as $forbidden) {
    fail_if(str_contains($api, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
contains_text($ui, 'Execute Runtime Write', 'Builder UI');
contains_text($ui, 'window.confirm', 'Builder UI destructive confirmation');
contains_text($ui, 'window.prompt', 'Builder UI typed confirmation');
contains_text($ui, 'does not publish, run migrations, register routes, mark the module published, or execute rollback', 'Builder UI safety notice');
foreach (['Deploy', 'Run migrations', 'Mark published', 'Copy to Runtime'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI label exists: {$forbidden}");
}

$contract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-execution-mvp-contract.json');
$finalContract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-execution-final-implementation-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$toolRegistry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');
$mcp = json_contract('docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');

fail_if(($contract['current_implementation_status'] ?? null) !== 'runtime_write_execution_mvp', 'Runtime write execution contract must mark MVP status.');
fail_if(($contract['runtime_write_endpoint_implemented'] ?? null) !== true, 'Runtime write endpoint must be implemented in contract.');
fail_if(($contract['publish_executed'] ?? null) !== false, 'Runtime write execution contract must keep publish false.');
fail_if(($contract['migrations_run_in_runtime_write_phase'] ?? null) !== false, 'Runtime write execution contract must forbid migrations.');
fail_if(($finalContract['runtime_write_service_implemented'] ?? null) !== true, 'Final implementation contract must mark service implemented.');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'runtime write execution MVP', 'RAG manifest');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'bypass runtime write typed confirmation', 'Safety boundaries');
fail_if(($toolRegistry['execute_runtime_write_tool_available_to_ai_autonomously'] ?? null) !== false, 'Tool Registry must not expose execute runtime write autonomously.');
fail_if(($toolRegistry['execute_runtime_write_safe_for_agent'] ?? null) !== false, 'Tool Registry must mark execute runtime write unsafe for agent.');
contains_text(json_encode($mcp, JSON_PRETTY_PRINT) ?: '', 'mcp_must_not_expose_execute_runtime_write', 'MCP contract');

foreach (['modules/Core', 'modules/SaaS', 'modules/Updater', 'modules/Installer', 'package.json', 'composer.json', 'public/build'] as $path) {
    $status = [];
    exec('cd '.escapeshellarg($root).' && git -c safe.directory='.escapeshellarg($root).' --no-pager status --short -- '.escapeshellarg($path), $status);
    fail_if($status !== [], "Forbidden path has changes: {$path} ".implode('; ', $status));
}

if (! Schema::hasTable('builder_publish_executions')) {
    $errors[] = 'builder_publish_executions table is missing. Run migrations first.';
}

if ($errors === []) {
    try {
        [$disabledDefinition, $disabledExecution] = run_chain('BuilderRuntimeWriteDisabled'.time(), false);
        $disabledReport = app(BuilderRuntimeWriteExecutionService::class)->execute($disabledExecution->fresh());
        fail_if(($disabledReport['status'] ?? null) !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_ABORTED, 'Disabled config must abort runtime write.');
        fail_if(($disabledReport['runtime_write_executed'] ?? null) !== false, 'Disabled config must not execute runtime write.');
        fail_if(($disabledReport['runtime_writes_performed'] ?? null) !== 0, 'Disabled config must perform zero runtime writes.');
        fail_if(($disabledReport['copy_to_runtime_executed'] ?? null) !== false, 'Disabled config must not copy to runtime.');
        fail_if(($disabledReport['publish_executed'] ?? null) !== false, 'Disabled config must not publish.');
        fail_if(is_dir(project_path('modules/'.$disabledDefinition->module_name)), 'Disabled config created runtime module directory.');

        $moduleName = 'BuilderRuntimeWriteTest'.time();
        [$enabledDefinition, $enabledExecution] = run_chain($moduleName, true);
        $enabledReport = app(BuilderRuntimeWriteExecutionService::class)->execute($enabledExecution->fresh());
        $cleanupPaths[] = 'modules/'.$moduleName;
        $cleanupPaths[] = 'storage/app/builder-runtime-write-executions/'.$enabledDefinition->getKey().'/'.$enabledExecution->getKey();

        fail_if(! is_file(project_path((string) ($enabledReport['runtime_write_report_path'] ?? ''))), 'Runtime write report was not written under storage.');
        fail_if(($enabledReport['runtime_write_executed'] ?? null) !== true, 'Enabled config must execute runtime write.');
        fail_if(($enabledReport['runtime_writes_performed'] ?? 0) <= 0, 'Enabled config must commit at least one runtime file.');
        fail_if(($enabledReport['publish_executed'] ?? null) !== false, 'Runtime write must not publish.');
        fail_if(($enabledReport['migrations_run'] ?? null) !== false, 'Runtime write must not run migrations.');
        fail_if(($enabledReport['routes_registered'] ?? null) !== false, 'Runtime write must not register routes.');
        fail_if(($enabledReport['module_marked_published'] ?? null) !== false, 'Runtime write must not mark module published.');
        fail_if(($enabledReport['rollback_executed'] ?? null) !== false, 'Runtime write must not execute rollback.');
        fail_if($enabledDefinition->fresh()->status === 'published', 'BuilderDefinition must not be marked published.');

        foreach ($enabledReport['files_committed'] ?? [] as $file) {
            $target = (string) ($file['future_runtime_path'] ?? '');
            fail_if(! str_starts_with($target, 'modules/'.$moduleName.'/'), "Committed file outside generated module: {$target}");
            fail_if(! is_file(project_path($target)), "Committed file missing: {$target}");
        }

        $rollback = json_decode((string) file_get_contents(project_path((string) $enabledExecution->fresh()->rollback_manifest_path)), true);
        fail_if(empty($rollback['committed_file_entries']), 'Rollback manifest missing committed file entries.');

        foreach (['modules/Core', 'modules/SaaS', 'modules/Updater', 'modules/Installer', 'public/build', 'vendor', 'node_modules'] as $forbiddenPath) {
            $status = [];
            exec('cd '.escapeshellarg($root).' && git -c safe.directory='.escapeshellarg($root).' --no-pager status --short -- '.escapeshellarg($forbiddenPath), $status);
            fail_if($status !== [], "Forbidden path changed during runtime check: {$forbiddenPath} ".implode('; ', $status));
        }

        fail_if(! BuilderPublishAuditLog::where('event_type', 'runtime_write_succeeded')->where('builder_definition_id', $enabledDefinition->getKey())->exists(), 'runtime_write_succeeded audit event missing.');
    } catch (Throwable $e) {
        $errors[] = 'Runtime verifier failed: '.$e->getMessage();
    }
}

foreach ($cleanupPaths as $path) {
    if ($path !== '' && (str_starts_with($path, 'modules/BuilderRuntimeWrite') || str_starts_with($path, 'storage/app/builder-'))) {
        File::deleteDirectory(project_path($path));
    }
}

foreach ($cleanupDefinitionIds as $definitionId) {
    BuilderPublishAuditLog::where('builder_definition_id', $definitionId)->delete();
    DB::table('builder_runtime_write_operator_acknowledgements')->where('builder_definition_id', $definitionId)->delete();
    DB::table('builder_runtime_write_final_confirmations')->where('builder_definition_id', $definitionId)->delete();
    DB::table('builder_publish_executions')->where('builder_definition_id', $definitionId)->delete();
    DB::table('builder_publish_approval_requests')->where('builder_definition_id', $definitionId)->delete();
    BuilderDefinition::whereKey($definitionId)->delete();
}

config([
    'builder.runtime_write.enabled' => $originalRuntimeWriteConfig,
    'builder.runtime_write.max_files_per_execution' => $originalMaxFiles,
    'builder.runtime_write.max_total_bytes_per_execution' => $originalMaxBytes,
]);

if ($errors !== []) {
    echo "FAIL\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "PASS\n";
echo "Builder runtime write execution MVP verified. Runtime write is guarded, publish remains false, migrations are not executed, and generated test runtime files were cleaned up.\n";
