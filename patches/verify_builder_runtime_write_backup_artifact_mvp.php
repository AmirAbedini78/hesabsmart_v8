<?php

declare(strict_types=1);

use App\Models\BuilderDefinition;
use App\Models\BuilderPublishApprovalRequest;
use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
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
    'app/Services/Builder/BuilderRuntimeWriteBackupArtifactService.php',
    'docs/ai/03-architecture/builder-runtime-write-backup-artifact-mvp.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-backup-artifact-contract.json',
    'docs/ai/04-docops/history/2026-07-05-builder-runtime-write-backup-artifact-mvp.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$service = read_project_file('app/Services/Builder/BuilderRuntimeWriteBackupArtifactService.php');
foreach ([
    'storage/app/builder-publish-backups',
    "'runtime_writes_performed' => 0",
    "'publish_executed' => false",
    "'copy_to_runtime_executed' => false",
] as $required) {
    contains_text($service, $required, 'runtime write backup artifact service');
}

$controller = read_project_file('app/Http/Controllers/Builder/BuilderPublishExecutionController.php');
contains_text($controller, 'runtimeWriteBackups', 'controller');

$routes = read_project_file('routes/api.php');
contains_text($routes, 'runtime-write-backups', 'routes');
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write route exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime route exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish route exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback route exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
contains_text($api, 'prepareRuntimeWriteBackups', 'builderApi');
foreach (['executeRuntimeWrite', 'copyToRuntime', 'publishDefinition', 'executePublish', 'rollbackPublish'] as $forbidden) {
    fail_if(str_contains($api, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
contains_text($ui, 'Prepare Runtime Write Backups', 'Builder UI');
contains_text($ui, 'Backup artifact only', 'Builder UI');
contains_text($ui, 'does not copy staged files to runtime, run migrations, register routes, or publish', 'Builder UI');
foreach (['Execute Runtime Write', 'Copy to Runtime', 'text="Publish"', 'Execute Publish', 'Deploy', 'text="Rollback"', 'Run migrations'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI text exists: {$forbidden}");
}

$contract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-backup-artifact-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$toolRegistry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');

fail_if(($contract['current_implementation_status'] ?? null) !== 'runtime_write_backup_artifact_mvp', 'Backup artifact contract must mark MVP status.');
fail_if(($contract['runtime_writes_performed'] ?? null) !== 0, 'Backup artifact contract runtime writes must be zero.');
fail_if(($contract['publish_executed'] ?? null) !== false, 'Backup artifact contract publish_executed must be false.');
fail_if(($contract['copy_to_runtime_executed'] ?? null) !== false, 'Backup artifact contract copy_to_runtime_executed must be false.');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'runtime write backup artifact', 'RAG manifest');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'autonomously prepare runtime write backup', 'Safety boundaries');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'treat runtime write backups as runtime write execution', 'Safety boundaries');
contains_text(json_encode($toolRegistry, JSON_PRETTY_PRINT) ?: '', 'builder.prepare_runtime_write_backups', 'Tool Registry');
fail_if(($toolRegistry['runtime_write_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement runtime write tool.');

foreach (glob(project_path('database/migrations/*runtime*write*backup*.php')) ?: [] as $migration) {
    $errors[] = 'Forbidden runtime write backup migration exists: '.str_replace($root.DIRECTORY_SEPARATOR, '', $migration);
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
$createdProjectPaths = [];

$createReadyExecution = function (string $moduleName) use (&$createdDefinitionIds): array {
    $approvalService = app(BuilderPublishApprovalService::class);
    $preparationService = app(BuilderPublishExecutionPreparationService::class);
    $validationService = app(BuilderPublishStagedFileValidationService::class);
    $planService = app(BuilderRuntimeWritePlanArtifactService::class);
    $confirmationService = app(BuilderRuntimeWriteFinalConfirmationService::class);
    $preflightService = app(BuilderRuntimeWriteExecutionPreflightService::class);

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
    File::put(project_path($execution->staging_root.'/backend/Model.php.stub'), "<?php\n// runtime write backup smoke\n");
    $validationService->validate($execution->fresh());
    $planService->plan($execution->fresh());
    $execution->refresh();
    $confirmation = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
    $confirmationService->grant($confirmation, 'backup artifact smoke grant');
    $preflightService->preflight($execution->fresh());
    $execution->refresh();
    fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_PREFLIGHT_PASSED, 'Execution should be runtime_write_preflight_passed.');

    return [$definition->fresh(), $execution->fresh()];
};

$injectExistingAllowedTarget = function (BuilderPublishExecution $execution, string $moduleName) use (&$createdProjectPaths): string {
    $metadata = $execution->metadata_json ?: [];
    $planPath = (string) ($metadata['runtime_write_plan_path'] ?? '');
    $plan = json_decode((string) file_get_contents(project_path($planPath)), true);
    $futureRuntimePath = 'docs/ai/generated-manifests/'.Str::studly($moduleName).'/existing-backup-smoke.json';

    File::ensureDirectoryExists(dirname(project_path($futureRuntimePath)));
    File::put(project_path($futureRuntimePath), json_encode(['existing' => true, 'module' => $moduleName], JSON_PRETTY_PRINT));
    $createdProjectPaths[] = $futureRuntimePath;

    $plan['planned_writes'][] = [
        'source_relative_path' => 'manifest/existing-backup-smoke.json',
        'source_sha256' => hash('sha256', 'storage-only-source'),
        'future_runtime_path' => $futureRuntimePath,
        'runtime_path_allowed' => true,
        'write_action' => 'overwrite',
        'backup_required' => true,
        'migration_execution_allowed_in_this_phase' => false,
        'runtime_written' => false,
    ];
    $plan['summary']['total_planned_writes'] = count($plan['planned_writes']);
    $plan['summary']['overwrites'] = ($plan['summary']['overwrites'] ?? 0) + 1;

    File::put(project_path($planPath), json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return $futureRuntimePath;
};

$injectForbiddenTarget = function (BuilderPublishExecution $execution): void {
    $metadata = $execution->metadata_json ?: [];
    $planPath = (string) ($metadata['runtime_write_plan_path'] ?? '');
    $plan = json_decode((string) file_get_contents(project_path($planPath)), true);

    $plan['planned_writes'][] = [
        'source_relative_path' => 'backend/Forbidden.php.stub',
        'source_sha256' => hash('sha256', 'forbidden-source'),
        'future_runtime_path' => 'modules/Core/ForbiddenRuntimeWrite.php',
        'runtime_path_allowed' => false,
        'write_action' => 'create',
        'backup_required' => false,
        'migration_execution_allowed_in_this_phase' => false,
        'runtime_written' => false,
    ];
    $plan['summary']['total_planned_writes'] = count($plan['planned_writes']);
    $plan['summary']['blocked'] = ($plan['summary']['blocked'] ?? 0) + 1;

    File::put(project_path($planPath), json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
};

try {
    if ($errors === []) {
        $backupService = app(BuilderRuntimeWriteBackupArtifactService::class);

        $moduleName = 'RuntimeBackupSmoke'.Str::random(8);
        [$definition, $execution] = $createReadyExecution($moduleName);
        $existingTargetPath = $injectExistingAllowedTarget($execution, $moduleName);
        $report = $backupService->prepare($execution->fresh());
        $execution->refresh();

        fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_BACKUPS_PREPARED, 'Execution should be runtime_write_backups_prepared.');
        fail_if(! str_starts_with((string) $report['backup_manifest_path'], 'storage/app/builder-publish-backups/'), 'Backup manifest path must be under storage.');
        fail_if(! is_file(project_path((string) $report['backup_manifest_path'])), 'Backup manifest file missing.');
        $manifest = json_decode((string) file_get_contents(project_path((string) $report['backup_manifest_path'])), true);
        fail_if(! is_array($manifest), 'Backup manifest JSON invalid.');
        fail_if(($report['runtime_writes_performed'] ?? null) !== 0, 'Runtime writes must be zero.');
        fail_if(($report['publish_executed'] ?? null) !== false, 'Publish executed must be false.');
        fail_if(($report['copy_to_runtime_executed'] ?? null) !== false, 'Copy to runtime must be false.');
        fail_if(($report['summary']['existing_files_backed_up'] ?? 0) < 1, 'Existing runtime-like target file should be backed up to storage.');

        $existingBackup = collect($report['backups'])->firstWhere('future_runtime_path', $existingTargetPath);
        fail_if(! is_array($existingBackup), 'Existing target backup entry missing.');
        fail_if(($existingBackup['backup_created'] ?? false) !== true, 'Existing target backup should be created.');
        fail_if(! str_starts_with((string) ($existingBackup['backup_path'] ?? ''), 'storage/app/builder-publish-backups/'), 'Existing target backup path must be under storage.');
        fail_if(! is_file(project_path((string) $existingBackup['backup_path'])), 'Existing target backup file missing.');
        fail_if(hash_file('sha256', project_path($existingTargetPath)) !== ($existingBackup['backup_sha256'] ?? null), 'Backup hash should match existing target file hash.');
        fail_if(is_dir(project_path('modules/'.$definition->module_name)), 'Runtime module directory was created.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'Definition was marked published.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_backup_created')->exists(), 'runtime_write_backup_created audit event missing.');

        $rollbackManifest = json_decode((string) file_get_contents(project_path($execution->rollback_manifest_path)), true);
        fail_if(! isset($rollbackManifest['runtime_write_backups']), 'Rollback manifest should reference runtime write backups.');

        [$blockedDefinition, $blockedExecution] = $createReadyExecution('RuntimeBackupBlocked'.Str::random(8));
        $injectForbiddenTarget($blockedExecution);
        $blockedReport = $backupService->prepare($blockedExecution->fresh());
        $blockedExecution->refresh();
        fail_if($blockedExecution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_BACKUP_BLOCKED, 'Forbidden target should block backup preparation.');
        fail_if(empty($blockedReport['blockers'] ?? []), 'Forbidden target should produce blockers.');
        fail_if(($blockedReport['runtime_writes_performed'] ?? null) !== 0, 'Blocked path runtime writes must be zero.');
        fail_if(($blockedReport['publish_executed'] ?? null) !== false, 'Blocked path publish_executed must be false.');
        fail_if(($blockedReport['copy_to_runtime_executed'] ?? null) !== false, 'Blocked path copy_to_runtime_executed must be false.');
        fail_if(is_dir(project_path('modules/'.$blockedDefinition->module_name)), 'Blocked path created runtime module directory.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $blockedDefinition->getKey())->where('event_type', 'runtime_write_backup_blocked')->exists(), 'runtime_write_backup_blocked audit event missing.');
    }
} finally {
    foreach ($createdProjectPaths as $path) {
        if (is_file(project_path($path))) {
            File::delete(project_path($path));
        }
        $directory = dirname(project_path($path));
        if (is_dir($directory) && count(File::files($directory)) === 0 && count(File::directories($directory)) === 0) {
            File::deleteDirectory($directory);
        }
    }

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
echo "Builder runtime write backup artifact MVP verified. Backups stay under storage and runtime writes remain zero.\n";
