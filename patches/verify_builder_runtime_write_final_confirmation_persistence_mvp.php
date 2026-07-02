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
    'database/migrations/2026_07_02_000007_create_builder_runtime_write_final_confirmations_table.php',
    'app/Models/BuilderRuntimeWriteFinalConfirmation.php',
    'app/Services/Builder/BuilderRuntimeWriteFinalConfirmationService.php',
    'app/Http/Controllers/Builder/BuilderRuntimeWriteFinalConfirmationController.php',
    'docs/ai/03-architecture/builder-runtime-write-final-confirmation-persistence-mvp.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-final-confirmation-api-map.json',
    'docs/ai/04-docops/history/2026-07-02-builder-runtime-write-final-confirmation-persistence-mvp.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$routes = read_project_file('routes/api.php');
foreach ([
    'runtime-write-final-confirmations',
    'runtime-write-final-confirmations/{confirmation}/grant',
    'runtime-write-final-confirmations/{confirmation}/reject',
    'runtime-write-final-confirmations/{confirmation}/revoke',
] as $required) {
    contains_text($routes, $required, 'routes');
}
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write endpoint exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime endpoint exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish endpoint exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback endpoint exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
foreach ([
    'listRuntimeWriteFinalConfirmations',
    'requestRuntimeWriteFinalConfirmation',
    'grantRuntimeWriteFinalConfirmation',
    'rejectRuntimeWriteFinalConfirmation',
    'revokeRuntimeWriteFinalConfirmation',
] as $required) {
    contains_text($api, $required, 'builderApi');
}
foreach (['executeRuntimeWrite', 'copyToRuntime', 'publishDefinition', 'executePublish', 'rollbackPublish'] as $forbidden) {
    fail_if(str_contains($api, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
contains_text($ui, 'Request Runtime Write Final Confirmation', 'Builder UI');
contains_text($ui, 'Grant Confirmation', 'Builder UI');
contains_text($ui, 'Reject Confirmation', 'Builder UI');
contains_text($ui, 'Revoke Confirmation', 'Builder UI');
contains_text($ui, 'Final confirmation only', 'Builder UI');
contains_text($ui, 'does not write runtime files, copy staged artifacts, run migrations, register routes, or publish', 'Builder UI');
foreach (['Execute Runtime Write', 'Copy to Runtime', 'text="Publish"', 'Execute Publish', 'Deploy', 'text="Rollback"', 'Run migrations'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI text exists: {$forbidden}");
}

$apiMap = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-final-confirmation-api-map.json');
$contract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-final-confirmation-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$mcp = json_contract('docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');

fail_if(($apiMap['current_implementation_status'] ?? null) !== 'final_confirmation_persistence_mvp', 'API map must mark final confirmation persistence MVP.');
fail_if(($apiMap['runtime_writes_performed'] ?? null) !== 0, 'API map runtime writes must be zero.');
fail_if(($apiMap['publish_executed'] ?? null) !== false, 'API map publish_executed must be false.');
fail_if(($apiMap['copy_to_runtime_executed'] ?? null) !== false, 'API map copy_to_runtime_executed must be false.');
fail_if(($contract['current_implementation_status'] ?? null) !== 'final_confirmation_persistence_mvp', 'Final confirmation contract must mark persistence MVP.');
fail_if(($contract['final_confirmation_persistence_implemented'] ?? null) !== true, 'Final confirmation persistence must be true.');
fail_if(($contract['runtime_write_endpoint_implemented'] ?? null) !== false, 'Runtime write endpoint must remain false.');
fail_if(($contract['confirmation_does_not_publish'] ?? null) !== true, 'Confirmation must not publish.');
fail_if(($contract['confirmation_does_not_write_runtime'] ?? null) !== true, 'Confirmation must not write runtime.');
fail_if(($contract['ai_may_confirm'] ?? null) !== false, 'AI may confirm must be false.');
fail_if(($contract['mcp_may_confirm'] ?? null) !== false, 'MCP may confirm must be false.');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'final confirmation persistence', 'RAG manifest');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'autonomously request runtime write final confirmation', 'Safety boundaries');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'ai_builder_agent_may_execute_runtime_write', 'Safety boundaries');
fail_if(($mcp['mcp_must_not_expose_final_confirmation_tools'] ?? null) !== true, 'MCP must not expose confirmation tools.');
fail_if(($mcp['mcp_must_not_expose_runtime_write_tools'] ?? null) !== true, 'MCP must not expose runtime write tools.');

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

if (! Schema::hasTable('builder_runtime_write_final_confirmations')) {
    $errors[] = 'builder_runtime_write_final_confirmations table is missing. Run: docker compose exec app php artisan migrate';
}

$createdDefinitionIds = [];

try {
    if ($errors === []) {
        $approvalService = app(BuilderPublishApprovalService::class);
        $preparationService = app(BuilderPublishExecutionPreparationService::class);
        $validationService = app(BuilderPublishStagedFileValidationService::class);
        $planService = app(BuilderRuntimeWritePlanArtifactService::class);
        $confirmationService = app(BuilderRuntimeWriteFinalConfirmationService::class);

        $moduleName = 'FinalConfirmationSmoke'.Str::random(8);
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

        $approval = $approvalService->approve($approvalService->requestApproval($definition));
        fail_if($approval->status !== BuilderPublishApprovalRequest::STATUS_APPROVED, 'Approval request was not approved.');

        $executionReport = $preparationService->prepare($definition->fresh());
        $execution = BuilderPublishExecution::findOrFail($executionReport['execution_id']);
        File::ensureDirectoryExists(project_path($execution->staging_root.'/backend'));
        File::put(project_path($execution->staging_root.'/backend/Model.php.stub'), "<?php\n// final confirmation smoke\n");
        $validationService->validate($execution->fresh());
        $planService->plan($execution->fresh());
        $execution->refresh();
        fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_PLANNED, 'Execution should be runtime_write_planned.');

        $requestReport = $confirmationService->request($execution->fresh());
        $confirmation = BuilderRuntimeWriteFinalConfirmation::findOrFail($requestReport['confirmation_id']);
        fail_if($confirmation->status !== BuilderRuntimeWriteFinalConfirmation::STATUS_REQUESTED, 'Confirmation should be requested.');
        fail_if(empty($confirmation->runtime_write_plan_path), 'Confirmation missing runtime_write_plan_path.');
        fail_if(empty($confirmation->definition_checksum), 'Confirmation missing definition_checksum.');
        fail_if(empty($confirmation->candidate_id), 'Confirmation missing candidate_id.');
        fail_if(empty($confirmation->staged_validation_report_path), 'Confirmation missing staged_validation_report_path.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_confirmation_requested')->exists(), 'runtime_write_confirmation_requested audit missing.');

        $grantReport = $confirmationService->grant($confirmation->fresh(), 'smoke grant');
        $confirmation->refresh();
        fail_if($confirmation->status !== BuilderRuntimeWriteFinalConfirmation::STATUS_GRANTED, 'Confirmation should be granted.');
        fail_if(($grantReport['runtime_writes_performed'] ?? null) !== 0, 'Grant runtime writes must be zero.');
        fail_if(($grantReport['publish_executed'] ?? null) !== false, 'Grant publish_executed must be false.');
        fail_if(($grantReport['copy_to_runtime_executed'] ?? null) !== false, 'Grant copy_to_runtime_executed must be false.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_confirmation_granted')->exists(), 'runtime_write_confirmation_granted audit missing.');
        fail_if(is_dir(project_path('modules/'.$moduleName)), 'Runtime module directory was created.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'Builder definition was marked published.');

        $reject = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
        $confirmationService->reject($reject, 'smoke reject');
        fail_if($reject->fresh()->status !== BuilderRuntimeWriteFinalConfirmation::STATUS_REJECTED, 'Confirmation should be rejected.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_confirmation_rejected')->exists(), 'runtime_write_confirmation_rejected audit missing.');

        $revoke = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
        $confirmationService->revoke($revoke, 'smoke revoke');
        fail_if($revoke->fresh()->status !== BuilderRuntimeWriteFinalConfirmation::STATUS_REVOKED, 'Confirmation should be revoked.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_confirmation_revoked')->exists(), 'runtime_write_confirmation_revoked audit missing.');

        $stale = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
        $definition->forceFill(['checksum' => 'stale-'.Str::random(12)])->save();
        $staleReport = $confirmationService->grant($stale, 'stale grant');
        fail_if($stale->fresh()->status !== BuilderRuntimeWriteFinalConfirmation::STATUS_INVALIDATED, 'Stale confirmation should be invalidated.');
        fail_if(($staleReport['runtime_writes_performed'] ?? null) !== 0, 'Stale path runtime writes must be zero.');
        fail_if(($staleReport['publish_executed'] ?? null) !== false, 'Stale path publish_executed must be false.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_confirmation_invalidated')->exists(), 'runtime_write_confirmation_invalidated audit missing.');
        fail_if(is_dir(project_path('modules/'.$moduleName)), 'Stale path created runtime module directory.');
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
echo "Builder runtime write final confirmation persistence MVP verified. Runtime writes remain zero and no publish/copy action exists.\n";
