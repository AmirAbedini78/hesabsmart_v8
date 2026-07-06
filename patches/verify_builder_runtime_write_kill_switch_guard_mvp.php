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
use App\Services\Builder\BuilderRuntimeWriteKillSwitchGuardService;
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
    'config/builder.php',
    'app/Services/Builder/BuilderRuntimeWriteKillSwitchGuardService.php',
    'docs/ai/03-architecture/builder-runtime-write-kill-switch-guard-mvp.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-kill-switch-guard-contract.json',
    'docs/ai/04-docops/history/2026-07-06-builder-runtime-write-kill-switch-guard-mvp.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$config = read_project_file('config/builder.php');
contains_text($config, "'enabled' => env('BUILDER_RUNTIME_WRITE_ENABLED', false)", 'builder config');
contains_text($config, "'max_files_per_execution' => env('BUILDER_RUNTIME_WRITE_MAX_FILES', 25)", 'builder config');
contains_text($config, "'max_total_bytes_per_execution' => env('BUILDER_RUNTIME_WRITE_MAX_BYTES', 5242880)", 'builder config');

$envStatus = [];
exec('cd '.escapeshellarg($root).' && git -c safe.directory='.escapeshellarg($root).' --no-pager status --short -- .env', $envStatus);
fail_if($envStatus !== [], '.env must not be modified.');

$service = read_project_file('app/Services/Builder/BuilderRuntimeWriteKillSwitchGuardService.php');
foreach ([
    'storage/app/builder-runtime-write-guards',
    "'runtime_writes_performed' => 0",
    "'publish_executed' => false",
    "'copy_to_runtime_executed' => false",
    "'runtime_write_guard_passed_is_not_execution' => true",
    'runtime_write_kill_switch_checked',
    'runtime_write_kill_switch_blocked',
    'runtime_write_kill_switch_passed',
] as $required) {
    contains_text($service, $required, 'kill-switch guard service');
}

$controller = read_project_file('app/Http/Controllers/Builder/BuilderPublishExecutionController.php');
contains_text($controller, 'runtimeWriteKillSwitchGuard', 'controller');

$routes = read_project_file('routes/api.php');
contains_text($routes, 'runtime-write-kill-switch-guard', 'routes');
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write route exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime route exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish route exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback route exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
contains_text($api, 'checkRuntimeWriteKillSwitchGuard', 'builderApi');
foreach (['executeRuntimeWrite', 'copyToRuntime', 'publishDefinition', 'executePublish', 'rollbackPublish', 'overrideKillSwitch'] as $forbidden) {
    fail_if(str_contains($api, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
contains_text($ui, 'Check Runtime Write Kill-Switch', 'Builder UI');
contains_text($ui, 'Kill-switch guard only', 'Builder UI');
contains_text($ui, 'does not execute runtime write, copy staged files, run migrations, register routes, or publish', 'Builder UI');
foreach (['Enable Runtime Write', 'Override Kill-Switch', 'Execute Runtime Write', 'Copy to Runtime', 'text="Publish"', 'Execute Publish', 'Deploy', 'text="Rollback"', 'Run migrations'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI text exists: {$forbidden}");
}

$contract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-kill-switch-guard-contract.json');
$killSwitchContract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-kill-switch-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$toolRegistry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');
$mcp = json_contract('docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');

fail_if(($contract['current_implementation_status'] ?? null) !== 'runtime_write_kill_switch_guard_mvp', 'Guard contract must mark MVP status.');
fail_if(($contract['default_enabled'] ?? null) !== false, 'Guard contract default_enabled must be false.');
fail_if(($contract['runtime_writes_performed'] ?? null) !== 0, 'Guard contract runtime writes must be zero.');
fail_if(($contract['publish_executed'] ?? null) !== false, 'Guard contract publish_executed must be false.');
fail_if(($contract['copy_to_runtime_executed'] ?? null) !== false, 'Guard contract copy_to_runtime_executed must be false.');
fail_if(($contract['runtime_write_guard_passed_is_not_execution'] ?? null) !== true, 'Guard passed flag must not be execution.');
fail_if(($contract['agent_may_override_kill_switch'] ?? null) !== false, 'Agent must not override kill-switch.');
fail_if(($contract['mcp_may_override_kill_switch'] ?? null) !== false, 'MCP must not override kill-switch.');
fail_if(($killSwitchContract['kill_switch_guard_implemented'] ?? null) !== true, 'Kill-switch contract must mention guard implemented.');
fail_if(($killSwitchContract['runtime_write_enabled_default'] ?? null) !== false, 'Kill-switch contract default must remain false.');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'runtime write kill-switch guard', 'RAG manifest');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'autonomously run runtime write kill-switch guard', 'Safety boundaries');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'override kill-switch', 'Safety boundaries');
contains_text(json_encode($toolRegistry, JSON_PRETTY_PRINT) ?: '', 'builder.check_runtime_write_kill_switch_guard', 'Tool Registry');
fail_if(($toolRegistry['runtime_write_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement runtime write tool.');
fail_if(($toolRegistry['kill_switch_override_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement kill-switch override tool.');
contains_text(json_encode($mcp, JSON_PRETTY_PRINT) ?: '', 'kill_switch_override', 'MCP contract');

foreach (glob(project_path('database/migrations/*kill*switch*.php')) ?: [] as $migration) {
    $errors[] = 'Forbidden kill-switch migration exists: '.str_replace($root.DIRECTORY_SEPARATOR, '', $migration);
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

foreach ([
    'builder_definitions',
    'builder_publish_approval_requests',
    'builder_publish_audit_logs',
    'builder_publish_executions',
    'builder_runtime_write_final_confirmations',
] as $table) {
    fail_if(! Schema::hasTable($table), "{$table} table is missing.");
}

$createdDefinitionIds = [];
$storagePaths = [];

$createReadyExecution = function (string $moduleName) use (&$createdDefinitionIds, &$storagePaths): array {
    $approvalService = app(BuilderPublishApprovalService::class);
    $preparationService = app(BuilderPublishExecutionPreparationService::class);
    $validationService = app(BuilderPublishStagedFileValidationService::class);
    $planService = app(BuilderRuntimeWritePlanArtifactService::class);
    $confirmationService = app(BuilderRuntimeWriteFinalConfirmationService::class);
    $preflightService = app(BuilderRuntimeWriteExecutionPreflightService::class);
    $backupService = app(BuilderRuntimeWriteBackupArtifactService::class);
    $readinessService = app(BuilderPostBackupRuntimeWriteReadinessService::class);

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
    $storagePaths[] = $execution->staging_root;
    $storagePaths[] = $execution->rollback_manifest_path ? dirname($execution->rollback_manifest_path) : null;

    File::ensureDirectoryExists(project_path($execution->staging_root.'/backend'));
    File::put(project_path($execution->staging_root.'/backend/Model.php.stub'), "<?php\n// kill-switch guard smoke\n");

    $validationService->validate($execution->fresh());
    $planService->plan($execution->fresh());
    $execution->refresh();
    $confirmation = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
    $confirmationService->grant($confirmation, 'kill-switch guard smoke grant');
    $preflightService->preflight($execution->fresh());
    $backupService->prepare($execution->fresh());
    $readinessService->readiness($execution->fresh());
    $execution->refresh();
    fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_READINESS_PASSED, 'Execution should be runtime_write_readiness_passed.');

    return [$definition->fresh(), $execution->fresh()];
};

try {
    if ($errors === []) {
        config(['builder.runtime_write.enabled' => false]);
        [$definition, $execution] = $createReadyExecution('GuardSmoke'.Str::random(8));

        $guardService = app(BuilderRuntimeWriteKillSwitchGuardService::class);
        $report = $guardService->check($execution->fresh());
        $execution->refresh();
        $storagePaths[] = $report['guard_report_path'] ? dirname($report['guard_report_path']) : null;
        $metadata = $execution->metadata_json ?: [];

        fail_if($report['runtime_write_enabled'] !== false, 'Default runtime_write_enabled should be false.');
        fail_if($report['status'] !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_BLOCKED, 'Default guard status should be runtime_write_guard_blocked.');
        fail_if($report['runtime_write_guard_passed'] !== false, 'Default guard must not pass.');
        fail_if(($report['runtime_write_guard_passed_is_not_execution'] ?? null) !== true, 'Guard passed flag must not be execution.');
        fail_if(($report['runtime_writes_performed'] ?? null) !== 0, 'Runtime writes must remain zero.');
        fail_if(($report['publish_executed'] ?? null) !== false, 'Publish must remain false.');
        fail_if(($report['copy_to_runtime_executed'] ?? null) !== false, 'Copy to runtime must remain false.');
        fail_if(! is_file(project_path($report['guard_report_path'] ?? '')), 'Guard report file missing.');
        fail_if(json_decode((string) file_get_contents(project_path($report['guard_report_path'])), true) === null, 'Guard report JSON invalid.');
        fail_if(($metadata['runtime_write_kill_switch_guard_path'] ?? null) !== $report['guard_report_path'], 'Execution metadata missing guard path.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_kill_switch_checked')->exists(), 'Audit event runtime_write_kill_switch_checked missing.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_kill_switch_blocked')->exists(), 'Audit event runtime_write_kill_switch_blocked missing.');
        fail_if(is_dir(project_path('modules/'.$definition->module_name)), 'Runtime module directory was created.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'BuilderDefinition must not be published.');

        config(['builder.runtime_write.enabled' => true]);
        $passedReport = $guardService->check($execution->fresh());
        $storagePaths[] = $passedReport['guard_report_path'] ? dirname($passedReport['guard_report_path']) : null;
        fail_if($passedReport['runtime_write_enabled'] !== true, 'Enabled config should be visible only in guard report.');
        fail_if($passedReport['status'] !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_PASSED, 'Enabled config should allow guard report to pass.');
        fail_if(($passedReport['runtime_write_guard_passed_is_not_execution'] ?? null) !== true, 'Passed guard must still not be execution.');
        fail_if(($passedReport['runtime_writes_performed'] ?? null) !== 0, 'Passed guard must still perform zero runtime writes.');
        fail_if(($passedReport['publish_executed'] ?? null) !== false, 'Passed guard must still not publish.');
        fail_if(($passedReport['copy_to_runtime_executed'] ?? null) !== false, 'Passed guard must still not copy to runtime.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_kill_switch_passed')->exists(), 'Audit event runtime_write_kill_switch_passed missing.');
        fail_if(is_dir(project_path('modules/'.$definition->module_name)), 'Runtime module directory was created after enabled guard check.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'BuilderDefinition must not be published after enabled guard check.');
        config(['builder.runtime_write.enabled' => false]);
    }
} catch (Throwable $e) {
    $errors[] = 'Runtime smoke failed: '.$e->getMessage();
} finally {
    foreach ($createdDefinitionIds as $definitionId) {
        $executionIds = BuilderPublishExecution::where('builder_definition_id', $definitionId)->pluck('id')->all();
        $storagePaths[] = 'storage/app/builder-runtime-write-guards/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-runtime-write-readiness/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-publish-backups/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-runtime-write-preflights/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-runtime-write-plans/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-publish-staged-validations/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-publish-staging/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-publish-rollbacks/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-publish-candidates/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-publish-dry-runs/'.$definitionId;
        $storagePaths[] = 'storage/app/builder-publish-readiness/'.$definitionId;

        BuilderRuntimeWriteFinalConfirmation::whereIn('builder_publish_execution_id', $executionIds)->delete();
        BuilderPublishAuditLog::where('builder_definition_id', $definitionId)->delete();
        BuilderPublishExecution::where('builder_definition_id', $definitionId)->delete();
        BuilderPublishApprovalRequest::where('builder_definition_id', $definitionId)->delete();
        BuilderDefinition::whereKey($definitionId)->delete();
    }

    foreach (array_filter(array_unique($storagePaths)) as $path) {
        if (str_starts_with($path, 'storage/app/builder-')) {
            File::deleteDirectory(project_path($path));
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
