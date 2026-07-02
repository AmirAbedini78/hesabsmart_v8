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
    'app/Services/Builder/BuilderRuntimeWriteExecutionPreflightService.php',
    'docs/ai/03-architecture/builder-runtime-write-execution-preflight-mvp.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-execution-preflight-contract.json',
    'docs/ai/04-docops/history/2026-07-03-builder-runtime-write-execution-preflight-mvp.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$service = read_project_file('app/Services/Builder/BuilderRuntimeWriteExecutionPreflightService.php');
foreach ([
    'storage/app/builder-runtime-write-preflights',
    "'runtime_writes_performed' => 0",
    "'publish_executed' => false",
    "'copy_to_runtime_executed' => false",
] as $required) {
    contains_text($service, $required, 'runtime write execution preflight service');
}

$controller = read_project_file('app/Http/Controllers/Builder/BuilderPublishExecutionController.php');
contains_text($controller, 'runtimeWritePreflight', 'controller');

$routes = read_project_file('routes/api.php');
contains_text($routes, 'runtime-write-preflight', 'routes');
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write route exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime route exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish route exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback route exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
contains_text($api, 'runRuntimeWriteExecutionPreflight', 'builderApi');
foreach (['executeRuntimeWrite', 'copyToRuntime', 'publishDefinition', 'executePublish', 'rollbackPublish'] as $forbidden) {
    fail_if(str_contains($api, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
contains_text($ui, 'Run Runtime Write Preflight', 'Builder UI');
contains_text($ui, 'Preflight only', 'Builder UI');
contains_text($ui, 'does not write runtime files, copy staged artifacts, run migrations, register routes, or publish', 'Builder UI');
foreach (['Execute Runtime Write', 'Copy to Runtime', 'text="Publish"', 'Execute Publish', 'Deploy', 'text="Rollback"', 'Run migrations'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI text exists: {$forbidden}");
}

$contract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-execution-preflight-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$toolRegistry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');

fail_if(($contract['current_implementation_status'] ?? null) !== 'runtime_write_execution_preflight_mvp', 'Preflight contract must mark MVP status.');
fail_if(($contract['runtime_writes_performed'] ?? null) !== 0, 'Preflight contract runtime writes must be zero.');
fail_if(($contract['publish_executed'] ?? null) !== false, 'Preflight contract publish_executed must be false.');
fail_if(($contract['copy_to_runtime_executed'] ?? null) !== false, 'Preflight contract copy_to_runtime_executed must be false.');
fail_if(($contract['ready_for_future_runtime_write_is_not_execution'] ?? null) !== true, 'Ready flag must not be execution.');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'runtime write execution preflight', 'RAG manifest');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'autonomously run runtime write execution preflight', 'Safety boundaries');
contains_text(json_encode($boundaries, JSON_PRETTY_PRINT) ?: '', 'ready_for_future_runtime_write', 'Safety boundaries');
contains_text(json_encode($toolRegistry, JSON_PRETTY_PRINT) ?: '', 'builder.run_runtime_write_preflight', 'Tool Registry');
fail_if(($toolRegistry['runtime_write_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement runtime write tool.');

foreach (glob(project_path('database/migrations/*runtime*write*preflight*.php')) ?: [] as $migration) {
    $errors[] = 'Forbidden runtime write preflight migration exists: '.str_replace($root.DIRECTORY_SEPARATOR, '', $migration);
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

if (! Schema::hasTable('builder_runtime_write_final_confirmations')) {
    $errors[] = 'builder_runtime_write_final_confirmations table is missing.';
}

$createdDefinitionIds = [];

$createReadyExecution = function (string $moduleName) use (&$createdDefinitionIds): array {
    $approvalService = app(BuilderPublishApprovalService::class);
    $preparationService = app(BuilderPublishExecutionPreparationService::class);
    $validationService = app(BuilderPublishStagedFileValidationService::class);
    $planService = app(BuilderRuntimeWritePlanArtifactService::class);
    $confirmationService = app(BuilderRuntimeWriteFinalConfirmationService::class);

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
    File::put(project_path($execution->staging_root.'/backend/Model.php.stub'), "<?php\n// runtime write preflight smoke\n");
    $validationService->validate($execution->fresh());
    $planService->plan($execution->fresh());
    $execution->refresh();
    fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_PLANNED, 'Execution should be runtime_write_planned.');
    $confirmation = BuilderRuntimeWriteFinalConfirmation::findOrFail($confirmationService->request($execution->fresh())['confirmation_id']);
    $confirmationService->grant($confirmation, 'preflight smoke grant');

    return [$definition->fresh(), $execution->fresh(), $confirmation->fresh()];
};

try {
    if ($errors === []) {
        $preflightService = app(BuilderRuntimeWriteExecutionPreflightService::class);

        [$definition, $execution] = $createReadyExecution('RuntimePreflightSmoke'.Str::random(8));
        $report = $preflightService->preflight($execution->fresh());
        $execution->refresh();

        fail_if($execution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_PREFLIGHT_PASSED, 'Execution should be runtime_write_preflight_passed.');
        fail_if(! str_starts_with((string) $report['preflight_report_path'], 'storage/app/builder-runtime-write-preflights/'), 'Preflight report path must be under storage.');
        fail_if(! is_file(project_path((string) $report['preflight_report_path'])), 'Preflight report file missing.');
        $reportJson = json_decode((string) file_get_contents(project_path((string) $report['preflight_report_path'])), true);
        fail_if(! is_array($reportJson), 'Preflight report JSON invalid.');
        fail_if(($report['ready_for_future_runtime_write'] ?? null) !== true, 'Ready execution should be ready for future runtime write.');
        fail_if(($report['runtime_writes_performed'] ?? null) !== 0, 'Runtime writes must be zero.');
        fail_if(($report['publish_executed'] ?? null) !== false, 'Publish executed must be false.');
        fail_if(($report['copy_to_runtime_executed'] ?? null) !== false, 'Copy to runtime must be false.');
        fail_if(is_dir(project_path('modules/'.$definition->module_name)), 'Runtime module directory was created.');
        fail_if($definition->fresh()->status === BuilderDefinition::STATUS_PUBLISHED, 'Definition was marked published.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $definition->getKey())->where('event_type', 'runtime_write_preflight_passed')->exists(), 'runtime_write_preflight_passed audit event missing.');

        [$staleDefinition, $staleExecution] = $createReadyExecution('RuntimePreflightStale'.Str::random(8));
        $staleDefinition->forceFill(['checksum' => 'stale-'.Str::random(12)])->save();
        $staleReport = $preflightService->preflight($staleExecution->fresh());
        $staleExecution->refresh();
        fail_if($staleExecution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_PREFLIGHT_BLOCKED, 'Stale execution should be runtime_write_preflight_blocked.');
        fail_if(($staleReport['ready_for_future_runtime_write'] ?? null) !== false, 'Stale execution should not be ready.');
        fail_if(empty($staleReport['blockers'] ?? []), 'Stale execution should have blockers.');
        fail_if(($staleReport['runtime_writes_performed'] ?? null) !== 0, 'Stale path runtime writes must be zero.');
        fail_if(($staleReport['publish_executed'] ?? null) !== false, 'Stale path publish_executed must be false.');
        fail_if(($staleReport['copy_to_runtime_executed'] ?? null) !== false, 'Stale path copy_to_runtime_executed must be false.');
        fail_if(is_dir(project_path('modules/'.$staleDefinition->module_name)), 'Stale path created runtime module directory.');
        fail_if(! BuilderPublishAuditLog::where('builder_definition_id', $staleDefinition->getKey())->where('event_type', 'runtime_write_preflight_blocked')->exists(), 'runtime_write_preflight_blocked audit event missing.');
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
echo "Builder runtime write execution preflight MVP verified. Runtime writes remain zero and no publish/copy action exists.\n";
