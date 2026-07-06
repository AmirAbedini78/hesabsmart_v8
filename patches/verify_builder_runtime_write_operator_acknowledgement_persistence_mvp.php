<?php

declare(strict_types=1);

use App\Models\BuilderDefinition;
use App\Models\BuilderPublishApprovalRequest;
use App\Models\BuilderPublishAuditLog;
use App\Models\BuilderPublishExecution;
use App\Models\BuilderRuntimeWriteFinalConfirmation;
use App\Models\BuilderRuntimeWriteOperatorAcknowledgement;
use App\Services\Builder\BuilderPostBackupRuntimeWriteReadinessService;
use App\Services\Builder\BuilderPublishApprovalService;
use App\Services\Builder\BuilderPublishExecutionPreparationService;
use App\Services\Builder\BuilderPublishStagedFileValidationService;
use App\Services\Builder\BuilderRuntimeWriteBackupArtifactService;
use App\Services\Builder\BuilderRuntimeWriteExecutionPreflightService;
use App\Services\Builder\BuilderRuntimeWriteFinalConfirmationService;
use App\Services\Builder\BuilderRuntimeWriteKillSwitchGuardService;
use App\Services\Builder\BuilderRuntimeWriteOperatorAcknowledgementService;
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
    'database/migrations/2026_07_02_000008_create_builder_runtime_write_operator_acknowledgements_table.php',
    'app/Models/BuilderRuntimeWriteOperatorAcknowledgement.php',
    'app/Services/Builder/BuilderRuntimeWriteOperatorAcknowledgementService.php',
    'app/Http/Controllers/Builder/BuilderRuntimeWriteOperatorAcknowledgementController.php',
    'docs/ai/03-architecture/builder-runtime-write-operator-acknowledgement-persistence-mvp.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-operator-acknowledgement-contract.json',
    'docs/ai/05-rag/contracts/builder-runtime-write-operator-acknowledgement-api-map.json',
    'docs/ai/04-docops/history/2026-07-06-builder-runtime-write-operator-acknowledgement-persistence-mvp.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$service = read_project_file('app/Services/Builder/BuilderRuntimeWriteOperatorAcknowledgementService.php');
foreach ([
    'runtime_write_operator_acknowledgement_requested',
    'runtime_write_operator_acknowledged',
    'runtime_write_operator_acknowledgement_revoked',
    'runtime_write_operator_acknowledgement_invalidated',
    "'runtime_writes_performed' => 0",
    "'publish_executed' => false",
    "'copy_to_runtime_executed' => false",
    "'acknowledgement_does_not_override_kill_switch' => true",
] as $required) {
    contains_text($service, $required, 'operator acknowledgement service');
}

$controller = read_project_file('app/Http/Controllers/Builder/BuilderRuntimeWriteOperatorAcknowledgementController.php');
contains_text($controller, 'acknowledge', 'operator acknowledgement controller');
contains_text($controller, 'revoke', 'operator acknowledgement controller');

$routes = read_project_file('routes/api.php');
foreach ([
    'runtime-write-operator-acknowledgements',
    'acknowledge',
    'revoke',
] as $required) {
    contains_text($routes, $required, 'routes');
}
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write route exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime route exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish route exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback route exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
foreach ([
    'listRuntimeWriteOperatorAcknowledgements',
    'requestRuntimeWriteOperatorAcknowledgement',
    'acknowledgeRuntimeWriteOperatorRunbook',
    'revokeRuntimeWriteOperatorAcknowledgement',
] as $required) {
    contains_text($api, $required, 'builderApi');
}
foreach (['executeRuntimeWrite', 'copyToRuntime', 'publishDefinition', 'executePublish', 'rollbackPublish', 'overrideKillSwitch', 'enableRuntimeWrite'] as $forbidden) {
    fail_if(str_contains($api, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
contains_text($ui, 'Request Operator Runbook Acknowledgement', 'Builder UI');
contains_text($ui, 'Acknowledge Runbook', 'Builder UI');
contains_text($ui, 'Revoke Acknowledgement', 'Builder UI');
contains_text($ui, 'Operator acknowledgement only', 'Builder UI');
contains_text($ui, 'does not execute runtime write, copy staged files, run migrations, register routes, or publish', 'Builder UI');
foreach (['Enable Runtime Write', 'Override Kill-Switch', 'Execute Runtime Write', 'Copy to Runtime', 'text="Publish"', 'Execute Publish', 'Deploy', 'text="Rollback"', 'Run migrations'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI text exists: {$forbidden}");
}

$contract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-operator-acknowledgement-contract.json');
$apiMap = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-operator-acknowledgement-api-map.json');
$runbook = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-operator-runbook-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$toolRegistry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');
$mcp = json_contract('docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');

fail_if(($contract['current_implementation_status'] ?? null) !== 'operator_acknowledgement_persistence_mvp', 'Acknowledgement contract must mark MVP status.');
fail_if(($contract['acknowledgement_does_not_write_runtime'] ?? null) !== true, 'Acknowledgement must not write runtime.');
fail_if(($contract['acknowledgement_does_not_override_kill_switch'] ?? null) !== true, 'Acknowledgement must not override kill-switch.');
fail_if(($apiMap['current_implementation_status'] ?? null) !== 'operator_acknowledgement_persistence_mvp', 'Acknowledgement API map must mark MVP status.');
fail_if(($runbook['operator_acknowledgement_persistence_implemented'] ?? null) !== true, 'Runbook contract must mark persistence implemented.');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'operator acknowledgement persistence', 'RAG manifest');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'autonomously acknowledge runtime write operator runbook', 'Safety boundaries');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'operator acknowledgement as runtime write execution', 'Safety boundaries');
contains_text(json_encode($toolRegistry, JSON_PRETTY_PRINT) ?: '', 'acknowledge_runtime_write_operator_runbook', 'Tool Registry');
fail_if(($toolRegistry['runtime_write_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement runtime write tool.');
fail_if(($toolRegistry['kill_switch_override_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement kill-switch override tool.');
contains_text(json_encode($mcp, JSON_PRETTY_PRINT) ?: '', 'mcp_must_not_expose_operator_acknowledgement_tools', 'MCP contract');
contains_text(json_encode($mcp, JSON_PRETTY_PRINT) ?: '', 'mcp_must_not_acknowledge_operator_runbook', 'MCP contract');

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
    '.env',
] as $path) {
    $status = [];
    exec('cd '.escapeshellarg($root).' && git -c safe.directory='.escapeshellarg($root).' --no-pager status --short -- '.escapeshellarg($path), $status);
    fail_if($status !== [], "Forbidden path has changes: {$path} ".implode('; ', $status));
}

if (! Schema::hasTable('builder_runtime_write_operator_acknowledgements')) {
    $errors[] = 'builder_runtime_write_operator_acknowledgements table is missing. Run: docker compose exec app php artisan migrate';
}

$createdDefinitionIds = [];
$storagePaths = [];

$createGuardedExecution = function (string $moduleName) use (&$createdDefinitionIds, &$storagePaths): array {
    $approvalService = app(BuilderPublishApprovalService::class);
    $preparationService = app(BuilderPublishExecutionPreparationService::class);
    $validationService = app(BuilderPublishStagedFileValidationService::class);
    $planService = app(BuilderRuntimeWritePlanArtifactService::class);
    $confirmationService = app(BuilderRuntimeWriteFinalConfirmationService::class);
    $preflightService = app(BuilderRuntimeWriteExecutionPreflightService::class);
    $backupService = app(BuilderRuntimeWriteBackupArtifactService::class);
    $readinessService = app(BuilderPostBackupRuntimeWriteReadinessService::class);
    $guardService = app(BuilderRuntimeWriteKillSwitchGuardService::class);

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
    File::put(project_path($execution->staging_root.'/backend/Model.php.stub'), "<?php\n// operator acknowledgement smoke\n");

    $validationService->validate($execution->fresh());
    $planService->plan($execution->fresh());
    $execution->refresh();
    $confirmation = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
    $confirmationService->grant($confirmation, 'operator acknowledgement verifier grant');
    $preflightService->preflight($execution->fresh());
    $backupService->prepare($execution->fresh());
    $readinessService->readiness($execution->fresh());
    $guardService->check($execution->fresh());
    $execution->refresh();
    fail_if(! in_array($execution->status, [BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_BLOCKED, BuilderPublishExecution::STATUS_RUNTIME_WRITE_GUARD_PASSED], true), 'Execution should have kill-switch guard status.');

    return [$definition->fresh(), $execution->fresh()];
};

try {
    if ($errors === []) {
        [$definition, $execution] = $createGuardedExecution('OperatorAckSmoke'.Str::random(8));
        $serviceInstance = app(BuilderRuntimeWriteOperatorAcknowledgementService::class);

        $requestReport = $serviceInstance->request($execution->fresh());
        $ack = BuilderRuntimeWriteOperatorAcknowledgement::findOrFail($requestReport['acknowledgement_id']);
        fail_if($ack->status !== BuilderRuntimeWriteOperatorAcknowledgement::STATUS_REQUESTED, 'Acknowledgement should be requested.');
        fail_if(blank($ack->runtime_write_plan_path) || blank($ack->post_backup_readiness_path) || blank($ack->kill_switch_guard_path), 'Binding paths must be stored.');
        fail_if(blank($ack->backup_manifest_path) || blank($ack->rollback_manifest_path), 'Backup and rollback paths must be stored.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_operator_acknowledgement_requested')->exists(), 'Requested audit event missing.');

        $ackReport = $serviceInstance->acknowledge($ack, 'operator acknowledgement verifier');
        $ack->refresh();
        fail_if($ack->status !== BuilderRuntimeWriteOperatorAcknowledgement::STATUS_ACKNOWLEDGED, 'Acknowledgement should be acknowledged.');
        fail_if(($ackReport['runtime_writes_performed'] ?? null) !== 0, 'Acknowledgement runtime writes must be zero.');
        fail_if(($ackReport['publish_executed'] ?? null) !== false, 'Acknowledgement publish_executed must be false.');
        fail_if(($ackReport['copy_to_runtime_executed'] ?? null) !== false, 'Acknowledgement copy_to_runtime must be false.');
        fail_if(($ackReport['acknowledgement_does_not_override_kill_switch'] ?? null) !== true, 'Acknowledgement must not override kill-switch.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_operator_acknowledged')->exists(), 'Acknowledged audit event missing.');
        fail_if(is_dir(project_path('modules/'.$definition->module_name)), 'Runtime module directory was created.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'BuilderDefinition must not be published.');

        $revoke = BuilderRuntimeWriteOperatorAcknowledgement::findOrFail($serviceInstance->request($execution->fresh())['acknowledgement_id']);
        $serviceInstance->revoke($revoke, 'operator acknowledgement verifier revoke');
        $revoke->refresh();
        fail_if($revoke->status !== BuilderRuntimeWriteOperatorAcknowledgement::STATUS_REVOKED, 'Acknowledgement should be revoked.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_operator_acknowledgement_revoked')->exists(), 'Revoked audit event missing.');

        $stale = BuilderRuntimeWriteOperatorAcknowledgement::findOrFail($serviceInstance->request($execution->fresh())['acknowledgement_id']);
        $definition->forceFill(['checksum' => hash('sha256', 'stale-'.$definition->getKey().Str::random())])->save();
        $staleReport = $serviceInstance->acknowledge($stale, 'operator acknowledgement verifier stale');
        $stale->refresh();
        fail_if($stale->status !== BuilderRuntimeWriteOperatorAcknowledgement::STATUS_INVALIDATED, 'Stale acknowledgement should be invalidated.');
        fail_if(($staleReport['runtime_writes_performed'] ?? null) !== 0, 'Stale path runtime writes must be zero.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_operator_acknowledgement_invalidated')->exists(), 'Invalidated audit event missing.');
        fail_if(is_dir(project_path('modules/'.$definition->module_name)), 'Runtime module directory was created after stale path.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'BuilderDefinition must not be published after stale path.');
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

        BuilderRuntimeWriteOperatorAcknowledgement::whereIn('builder_publish_execution_id', $executionIds)->delete();
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
