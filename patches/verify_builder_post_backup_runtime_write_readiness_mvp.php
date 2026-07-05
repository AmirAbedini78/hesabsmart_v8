<?php

declare(strict_types=1);

use App\Models\BuilderDefinition;
use App\Models\BuilderPublishApprovalRequest;
use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
use App\Services\Builder\BuilderPostBackupRuntimeWriteReadinessService;
use App\Services\Builder\BuilderPublishApprovalService;
use App\Services\Builder\BuilderPublishExecutionPreparationService;
use App\Services\Builder\BuilderPublishStagedFileValidationService;
use App\Services\Builder\BuilderRuntimeWriteBackupArtifactService;
use App\Services\Builder\BuilderRuntimeWriteExecutionPreflightService;
use App\Services\Builder\BuilderRuntimeWriteFinalConfirmationService;
use App\Services\Builder\BuilderRuntimeWritePlanArtifactService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$errors = [];

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

foreach ([
    'app/Services/Builder/BuilderPostBackupRuntimeWriteReadinessService.php',
    'docs/ai/03-architecture/builder-post-backup-runtime-write-readiness-mvp.md',
    'docs/ai/05-rag/contracts/builder-post-backup-runtime-write-readiness-contract.json',
    'docs/ai/04-docops/history/2026-07-05-builder-post-backup-runtime-write-readiness-mvp.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$service = read_project_file('app/Services/Builder/BuilderPostBackupRuntimeWriteReadinessService.php');
foreach ([
    'storage/app/builder-runtime-write-readiness',
    "'runtime_writes_performed' => 0",
    "'publish_executed' => false",
    "'copy_to_runtime_executed' => false",
    "'ready_for_runtime_write_execution_is_not_execution' => true",
] as $required) {
    contains_text($service, $required, 'post-backup readiness service');
}

$controller = read_project_file('app/Http/Controllers/Builder/BuilderPublishExecutionController.php');
contains_text($controller, 'postBackupRuntimeWriteReadiness', 'controller');

$routes = read_project_file('routes/api.php');
contains_text($routes, 'post-backup-runtime-write-readiness', 'routes');
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write route exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime route exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish route exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback route exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
contains_text($api, 'runPostBackupRuntimeWriteReadiness', 'builderApi');
foreach (['executeRuntimeWrite', 'copyToRuntime', 'publishDefinition', 'executePublish', 'rollbackPublish'] as $forbidden) {
    fail_if(str_contains($api, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
contains_text($ui, 'Check Post-Backup Runtime Write Readiness', 'Builder UI');
contains_text($ui, 'Readiness only', 'Builder UI');
contains_text($ui, 'does not copy staged files to runtime, run migrations, register routes, or publish', 'Builder UI');
foreach (['Execute Runtime Write', 'Copy to Runtime', 'text="Publish"', 'Execute Publish', 'Deploy', 'text="Rollback"', 'Run migrations'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI text exists: {$forbidden}");
}

$contract = json_contract('docs/ai/05-rag/contracts/builder-post-backup-runtime-write-readiness-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$toolRegistry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');

fail_if(($contract['current_implementation_status'] ?? null) !== 'post_backup_runtime_write_readiness_mvp', 'Readiness contract must mark MVP status.');
fail_if(($contract['runtime_writes_performed'] ?? null) !== 0, 'Readiness contract runtime writes must be zero.');
fail_if(($contract['publish_executed'] ?? null) !== false, 'Readiness contract publish_executed must be false.');
fail_if(($contract['copy_to_runtime_executed'] ?? null) !== false, 'Readiness contract copy_to_runtime_executed must be false.');
fail_if(($contract['ready_for_runtime_write_execution_is_not_execution'] ?? null) !== true, 'Readiness ready flag must not be execution.');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'post-backup runtime write readiness', 'RAG manifest');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'autonomously run post-backup runtime write readiness', 'Safety boundaries');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'ready_for_runtime_write_execution', 'Safety boundaries');
contains_text(json_encode($toolRegistry, JSON_PRETTY_PRINT) ?: '', 'builder.check_post_backup_runtime_write_readiness', 'Tool Registry');
fail_if(($toolRegistry['runtime_write_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement runtime write tool.');

foreach (glob(project_path('database/migrations/*post*backup*readiness*.php')) ?: [] as $migration) {
    $errors[] = 'Forbidden post-backup readiness migration exists: '.str_replace($root.DIRECTORY_SEPARATOR, '', $migration);
}

foreach ([
    'modules/Warehouse',
    'modules/Core',
    'modules/SaaS',
    'modules/Updater',
    'modules/Installer',
    'package.json',
    'composer.json',
    'public/build',
    'app/Console/Commands/ErpsmartMakeModuleCommand.php',
] as $path) {
    $status = [];
    exec('cd '.escapeshellarg($root).' && git -c safe.directory='.escapeshellarg($root).' --no-pager status --short -- '.escapeshellarg($path), $status);
    fail_if($status !== [], "Forbidden path has changes: {$path} ".implode('; ', $status));
}

if (! Schema::hasTable('builder_publish_executions')) {
    $errors[] = 'builder_publish_executions table is missing.';
}

$createdDefinitionIds = [];

$createBackedUpExecution = function (string $moduleName) use (&$createdDefinitionIds): array {
    $approvalService = app(BuilderPublishApprovalService::class);
    $preparationService = app(BuilderPublishExecutionPreparationService::class);
    $validationService = app(BuilderPublishStagedFileValidationService::class);
    $planService = app(BuilderRuntimeWritePlanArtifactService::class);
    $confirmationService = app(BuilderRuntimeWriteFinalConfirmationService::class);
    $preflightService = app(BuilderRuntimeWriteExecutionPreflightService::class);
    $backupService = app(BuilderRuntimeWriteBackupArtifactService::class);

    $definitionJson = temp_definition($moduleName);
    $definition = BuilderDefinition::create([
        'uuid' => (string) Str::uuid(),
        'name' => $moduleName,
        'slug' => Str::slug($moduleName),
        'module_name' => $moduleName,
        'entity_name' => $moduleName.' Record',
        'resource_name' => Str::kebab($moduleName).'-records',
        'status' => BuilderDefinition::STATUS_DRAFT,
        'schema_version' => 1,
        'definition_json' => $definitionJson,
        'checksum' => hash('sha256', json_encode($definitionJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
    ]);
    $createdDefinitionIds[] = $definition->getKey();

    $approvalService->approve($approvalService->requestApproval($definition));
    $executionReport = $preparationService->prepare($definition->fresh());
    $execution = BuilderPublishExecution::findOrFail($executionReport['execution_id']);
    File::ensureDirectoryExists(project_path($execution->staging_root.'/backend'));
    File::put(project_path($execution->staging_root.'/backend/Model.php.stub'), "<?php\n// post-backup readiness smoke\n");
    $validationService->validate($execution->fresh());
    $planService->plan($execution->fresh());
    $execution->refresh();
    $confirmation = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
    $confirmationService->grant($confirmation, 'post-backup readiness smoke grant');
    $preflightService->preflight($execution->fresh());
    $backupService->prepare($execution->fresh());
    $execution->refresh();
    fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_BACKUPS_PREPARED, 'Execution should be runtime_write_backups_prepared.');

    return [$definition->fresh(), $execution->fresh()];
};

try {
    if ($errors === []) {
        $readinessService = app(BuilderPostBackupRuntimeWriteReadinessService::class);

        [$definition, $execution] = $createBackedUpExecution('PostBackupReady'.Str::random(8));
        $report = $readinessService->readiness($execution->fresh());
        $execution->refresh();

        fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_READINESS_PASSED, 'Execution should be runtime_write_readiness_passed.');
        fail_if(! str_starts_with((string) $report['readiness_report_path'], 'storage/app/builder-runtime-write-readiness/'), 'Readiness report path must be under storage.');
        fail_if(! is_file(project_path((string) $report['readiness_report_path'])), 'Readiness report file missing.');
        $reportJson = json_decode((string) file_get_contents(project_path((string) $report['readiness_report_path'])), true);
        fail_if(! is_array($reportJson), 'Readiness report JSON invalid.');
        fail_if(($report['ready_for_runtime_write_execution'] ?? null) !== true, 'Ready execution should be ready for future runtime write execution.');
        fail_if(($report['ready_for_runtime_write_execution_is_not_execution'] ?? null) !== true, 'Ready flag must be marked as non-execution.');
        fail_if(($report['runtime_writes_performed'] ?? null) !== 0, 'Runtime writes must be zero.');
        fail_if(($report['publish_executed'] ?? null) !== false, 'Publish executed must be false.');
        fail_if(($report['copy_to_runtime_executed'] ?? null) !== false, 'Copy to runtime must be false.');
        fail_if(is_dir(project_path('modules/'.$definition->module_name)), 'Runtime module directory was created.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'Definition was marked published.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_readiness_passed')->exists(), 'runtime_write_readiness_passed audit event missing.');

        [$staleDefinition, $staleExecution] = $createBackedUpExecution('PostBackupStale'.Str::random(8));
        $metadata = $staleExecution->metadata_json ?: [];
        $backupManifestPath = (string) ($metadata['runtime_write_backup_manifest_path'] ?? '');
        if (is_file(project_path($backupManifestPath))) {
            File::delete(project_path($backupManifestPath));
        }
        $staleReport = $readinessService->readiness($staleExecution->fresh());
        $staleExecution->refresh();
        fail_if($staleExecution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_READINESS_BLOCKED, 'Stale backup manifest should block readiness.');
        fail_if(($staleReport['ready_for_runtime_write_execution'] ?? null) !== false, 'Stale path should not be ready.');
        fail_if(empty($staleReport['blockers'] ?? []), 'Stale path should have blockers.');
        fail_if(($staleReport['runtime_writes_performed'] ?? null) !== 0, 'Stale path runtime writes must be zero.');
        fail_if(($staleReport['publish_executed'] ?? null) !== false, 'Stale path publish_executed must be false.');
        fail_if(($staleReport['copy_to_runtime_executed'] ?? null) !== false, 'Stale path copy_to_runtime_executed must be false.');
        fail_if(is_dir(project_path('modules/'.$staleDefinition->module_name)), 'Stale path created runtime module directory.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $staleDefinition->getKey())->where('event_type', 'runtime_write_readiness_blocked')->exists(), 'runtime_write_readiness_blocked audit event missing.');
    }
} finally {
    foreach ($createdDefinitionIds as $definitionId) {
        BuilderRuntimeWriteFinalConfirmation::where('builder_definition_id', $definitionId)->delete();
        BuilderPublishAuditLog::where('builder_definition_id', $definitionId)->delete();
        BuilderPublishExecution::where('builder_definition_id', $definitionId)->delete();
        BuilderPublishApprovalRequest::where('builder_definition_id', $definitionId)->delete();
        BuilderDefinition::whereKey($definitionId)->delete();

        foreach ([
            'storage/app/builder-publish-candidates/'.$definitionId,
            'storage/app/builder-publish-dry-runs/'.$definitionId,
            'storage/app/builder-publish-readiness/'.$definitionId,
            'storage/app/builder-publish-staging/'.$definitionId,
            'storage/app/builder-publish-rollbacks/'.$definitionId,
            'storage/app/builder-publish-staged-validations/'.$definitionId,
            'storage/app/builder-runtime-write-plans/'.$definitionId,
            'storage/app/builder-runtime-write-preflights/'.$definitionId,
            'storage/app/builder-publish-backups/'.$definitionId,
            'storage/app/builder-runtime-write-readiness/'.$definitionId,
        ] as $directory) {
            if (is_dir(project_path($directory))) {
                File::deleteDirectory(project_path($directory));
            }
        }
    }
}

if ($errors !== []) {
    echo "FAIL\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "PASS\n";
echo "Builder post-backup runtime write readiness MVP verified. Readiness is storage-only and not runtime write execution.\n";
