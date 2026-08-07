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
use App\Services\Builder\BuilderRuntimeWritePostWriteSmokeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
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
    $fullPath = project_path($path);

    return is_file($fullPath) ? (string) file_get_contents($fullPath) : '';
}

function contains_text(string $contents, string $needle, string $label): void
{
    fail_if(! str_contains($contents, $needle), "{$label} missing {$needle}");
}

function json_contract(string $path): array
{
    $fullPath = project_path($path);
    fail_if(! is_file($fullPath), "Missing JSON contract: {$path}");
    if (! is_file($fullPath)) {
        return [];
    }
    $decoded = json_decode((string) file_get_contents($fullPath), true);
    fail_if(! is_array($decoded), "Invalid JSON contract: {$path}");

    return is_array($decoded) ? $decoded : [];
}

function definition_payload(string $moduleName): array
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
    $payload = definition_payload($moduleName);
    $definition = BuilderDefinition::create([
        'uuid' => (string) Str::uuid(),
        'name' => $moduleName,
        'slug' => Str::kebab($moduleName),
        'module_name' => $moduleName,
        'entity_name' => $moduleName.'Record',
        'resource_name' => Str::kebab($moduleName).'-records',
        'status' => 'validated',
        'schema_version' => 1,
        'definition_json' => $payload,
        'checksum' => hash('sha256', json_encode($payload)),
    ]);
    $cleanupDefinitionIds[] = $definition->getKey();

    return $definition;
}

function register_cleanup(BuilderDefinition $definition, BuilderPublishExecution $execution, string $moduleName): void
{
    global $cleanupPaths;
    $cleanupPaths[] = 'modules/'.$moduleName;
    foreach ([
        'storage/app/builder-publish-staging',
        'storage/app/builder-publish-rollbacks',
        'storage/app/builder-publish-staged-validations',
        'storage/app/builder-runtime-write-plans',
        'storage/app/builder-runtime-write-preflights',
        'storage/app/builder-publish-backups',
        'storage/app/builder-runtime-write-readiness',
        'storage/app/builder-runtime-write-guards',
        'storage/app/builder-runtime-write-executions',
        'storage/app/builder-runtime-write-smoke',
    ] as $rootPath) {
        $cleanupPaths[] = $rootPath.'/'.$definition->getKey().'/'.$execution->getKey();
    }
}

function seed_staged_files(BuilderPublishExecution $execution, string $moduleName): void
{
    $files = [
        "backend/{$moduleName}Model.php.stub" => "<?php\n\nnamespace Modules\\{$moduleName}\\App\\Models;\n\nclass {$moduleName}Model\n{\n    public string \$smoke = 'verified';\n}\n",
        "backend/{$moduleName}Migration.php.stub" => "<?php\n\nreturn new class\n{\n    public function up(): void {}\n};\n",
        "frontend/{$moduleName}Panel.vue" => "<template><div>Smoke verified</div></template>\n",
        'frontend/smoke-config.json' => json_encode(['smoke' => true], JSON_PRETTY_PRINT)."\n",
    ];
    foreach ($files as $relativePath => $contents) {
        $path = $execution->staging_root.'/'.$relativePath;
        File::ensureDirectoryExists(dirname(project_path($path)));
        File::put(project_path($path), $contents);
    }
}

function execute_full_chain(string $moduleName, bool $seedExistingTarget = false): array
{
    config([
        'builder.runtime_write.enabled' => true,
        'builder.runtime_write.max_files_per_execution' => 25,
        'builder.runtime_write.max_total_bytes_per_execution' => 5242880,
    ]);

    $definition = create_definition($moduleName);
    $approval = app(BuilderPublishApprovalService::class)->requestApproval($definition);
    app(BuilderPublishApprovalService::class)->approve($approval);
    $preparation = app(BuilderPublishExecutionPreparationService::class)->prepare($definition->fresh());
    $execution = BuilderPublishExecution::findOrFail($preparation['execution_id']);
    register_cleanup($definition, $execution, $moduleName);
    seed_staged_files($execution, $moduleName);
    if ($seedExistingTarget) {
        $existingTarget = 'modules/'.$moduleName.'/App/Models/'.$moduleName.'Model.php';
        File::ensureDirectoryExists(dirname(project_path($existingTarget)));
        File::put(project_path($existingTarget), "<?php\n// pre-existing generated test target\n");
    }

    app(BuilderPublishStagedFileValidationService::class)->validate($execution->fresh());
    app(BuilderRuntimeWritePlanArtifactService::class)->plan($execution->fresh());
    $confirmation = app(BuilderRuntimeWriteFinalConfirmationService::class)->request($execution->fresh());
    app(BuilderRuntimeWriteFinalConfirmationService::class)->grant($confirmation['confirmation']);
    app(BuilderRuntimeWriteExecutionPreflightService::class)->preflight($execution->fresh());
    app(BuilderRuntimeWriteBackupArtifactService::class)->prepare($execution->fresh());
    app(BuilderPostBackupRuntimeWriteReadinessService::class)->readiness($execution->fresh());
    app(BuilderRuntimeWriteKillSwitchGuardService::class)->check($execution->fresh());
    $acknowledgement = app(BuilderRuntimeWriteOperatorAcknowledgementService::class)->request($execution->fresh());
    app(BuilderRuntimeWriteOperatorAcknowledgementService::class)->acknowledge($acknowledgement['acknowledgement']);
    $runtimeWriteReport = app(BuilderRuntimeWriteExecutionService::class)->execute($execution->fresh());

    return [$definition->fresh(), $execution->fresh(), $runtimeWriteReport];
}

foreach ([
    'app/Services/Builder/BuilderRuntimeWritePostWriteSmokeService.php',
    'docs/ai/03-architecture/builder-runtime-write-post-write-smoke-mvp.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-post-write-smoke-contract.json',
    'docs/ai/04-docops/history/2026-07-14-builder-runtime-write-post-write-smoke-mvp.md',
] as $requiredPath) {
    fail_if(! is_file(project_path($requiredPath)), "Missing required file: {$requiredPath}");
}

$service = read_project_file('app/Services/Builder/BuilderRuntimeWritePostWriteSmokeService.php');
foreach ([
    'storage/app/builder-runtime-write-smoke',
    "'runtime_files_modified_by_smoke' => 0",
    "'runtime_writes_performed_by_smoke' => 0",
    "'publish_executed' => false",
    "'migrations_run' => false",
    "'routes_registered' => false",
    "'module_marked_published' => false",
    "'rollback_executed' => false",
    "PHP_BINARY.' -l '.escapeshellarg",
    'modules/Core',
    'modules/SaaS',
    'modules/Updater',
    'modules/Installer',
    'public/build',
    'vendor',
    'node_modules',
] as $needle) {
    contains_text($service, $needle, 'post-write smoke service');
}
fail_if((bool) preg_match('/\b(include|include_once|require|require_once)\s*\(?\s*\$?(fullPath|runtimePath)/', $service), 'Smoke service must not load generated PHP files.');

$controller = read_project_file('app/Http/Controllers/Builder/BuilderPublishExecutionController.php');
contains_text($controller, 'postWriteSmoke', 'execution controller');
$routes = read_project_file('routes/api.php');
contains_text($routes, 'post-write-smoke', 'routes');
foreach (['run-generated-migrations', 'register-generated-routes', 'mark-module-published', 'rollback-executions'] as $forbiddenRoute) {
    fail_if(str_contains($routes, $forbiddenRoute), "Forbidden route exists: {$forbiddenRoute}");
}

$api = read_project_file('modules/Builder/resources/js/services/builderApi.js');
contains_text($api, 'runRuntimeWritePostWriteSmoke', 'builderApi');
foreach (['publishDefinition', 'executePublish', 'runGeneratedMigrations', 'registerGeneratedRoutes', 'markModulePublished', 'rollbackPublish'] as $forbiddenMethod) {
    fail_if(str_contains($api, $forbiddenMethod), "Forbidden builderApi method exists: {$forbiddenMethod}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue');
contains_text($ui, 'Run Post-Write Smoke', 'Builder UI');
contains_text($ui, 'Post-write smoke only. This verifies committed runtime files and does not publish, run migrations, register routes, mark the module published, modify runtime files, or execute rollback.', 'Builder UI safety notice');
contains_text($ui, "latestExecution?.status !== 'runtime_write_succeeded'", 'Builder UI status gate');
foreach (['text="Deploy"', 'text="Run migrations"', 'text="Register routes"', 'text="Mark published"', 'text="Rollback"'] as $forbiddenLabel) {
    fail_if(str_contains($ui, $forbiddenLabel), "Forbidden UI action exists: {$forbiddenLabel}");
}

$contract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-post-write-smoke-contract.json');
$executionContract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-execution-mvp-contract.json');
$phaseContract = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-phase-contract.json');
$safetyContract = json_contract('docs/ai/05-rag/contracts/builder-publish-safety-contract.json');
$auditContract = json_contract('docs/ai/05-rag/contracts/builder-publish-audit-log-contract.json');
fail_if(($contract['current_implementation_status'] ?? null) !== 'runtime_write_post_write_smoke_mvp', 'Smoke contract must mark runtime_write_post_write_smoke_mvp.');
fail_if(($contract['runtime_files_may_be_modified'] ?? null) !== false, 'Smoke contract must forbid runtime file modification.');
fail_if(($contract['generated_php_code_execution_allowed'] ?? null) !== false, 'Smoke contract must forbid generated PHP execution.');
fail_if(($contract['publish_executed'] ?? null) !== false, 'Smoke contract must keep publish false.');
fail_if(($executionContract['post_write_smoke_implemented'] ?? null) !== true, 'Runtime write execution contract must link implemented post-write smoke.');
fail_if(($phaseContract['runtime_write_post_write_smoke_implemented'] ?? null) !== true, 'Runtime write phase contract must mark smoke implemented.');
fail_if(($safetyContract['current_runtime_write_post_write_smoke_mvp']['implemented'] ?? null) !== true, 'Publish safety contract must include post-write smoke MVP.');
foreach (['runtime_write_smoke_started', 'runtime_write_smoke_passed', 'runtime_write_smoke_failed', 'runtime_write_smoke_blocked'] as $event) {
    fail_if(! in_array($event, $auditContract['implemented_events'] ?? [], true), "Audit contract missing implemented event: {$event}");
}

$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$registry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');
$mcp = json_contract('docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');
contains_text(json_encode($manifest) ?: '', 'BuilderRuntimeWritePostWriteSmokeService', 'RAG manifest');
fail_if(($boundaries['lifecycle_planning_boundaries']['ai_builder_agent_may_autonomously_run_runtime_write_post_write_smoke'] ?? null) !== false, 'Safety boundaries must forbid autonomous smoke.');
contains_text(json_encode($registry) ?: '', 'builder.run_runtime_write_post_write_smoke', 'Tool Registry');
fail_if(($mcp['post_write_smoke_must_not_execute_generated_code'] ?? null) !== true, 'MCP contract must forbid generated code execution during smoke.');

foreach (['modules/Core', 'modules/Warehouse', 'modules/SaaS', 'modules/Updater', 'modules/Installer', 'package.json', 'composer.json', 'public/build'] as $forbiddenPath) {
    $status = [];
    exec('cd '.escapeshellarg($root).' && git -c safe.directory='.escapeshellarg($root).' --no-pager status --short -- '.escapeshellarg($forbiddenPath), $status);
    fail_if($status !== [], "Forbidden path has changes: {$forbiddenPath} ".implode('; ', $status));
}

if (! Schema::hasTable('builder_publish_executions')) {
    $errors[] = 'builder_publish_executions table is missing. Run migrations first.';
}

if ($errors === []) {
    try {
        $suffix = (string) time();
        [$successDefinition, $successExecution, $runtimeWriteReport] = execute_full_chain('BuilderSmokeSuccess'.$suffix, true);
        fail_if($successExecution->status !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_SUCCEEDED, 'Success execution must reach runtime_write_succeeded.');
        fail_if(($runtimeWriteReport['files_committed'] ?? []) === [], 'Runtime write must commit test files before smoke.');

        $beforeHashes = [];
        foreach ($runtimeWriteReport['files_committed'] as $file) {
            $path = (string) $file['future_runtime_path'];
            $beforeHashes[$path] = hash_file('sha256', project_path($path));
        }

        $smokeReport = app(BuilderRuntimeWritePostWriteSmokeService::class)->verify($successExecution->fresh());
        fail_if(($smokeReport['status'] ?? null) !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_PASSED, 'Clean runtime write must pass post-write smoke.');
        fail_if(($smokeReport['post_write_smoke_passed'] ?? null) !== true, 'Clean smoke report must mark passed true.');
        fail_if(! is_file(project_path((string) ($smokeReport['smoke_report_path'] ?? ''))), 'Smoke report must exist under storage.');
        fail_if(($smokeReport['runtime_files_modified_by_smoke'] ?? null) !== 0, 'Smoke must modify zero runtime files.');
        fail_if(($smokeReport['runtime_writes_performed_by_smoke'] ?? null) !== 0, 'Smoke must perform zero runtime writes.');
        fail_if(($smokeReport['publish_executed'] ?? null) !== false, 'Smoke must not publish.');
        fail_if(($smokeReport['migrations_run'] ?? null) !== false, 'Smoke must not run migrations.');
        fail_if(($smokeReport['routes_registered'] ?? null) !== false, 'Smoke must not register routes.');
        fail_if(($smokeReport['module_marked_published'] ?? null) !== false, 'Smoke must not mark module published.');
        fail_if(($smokeReport['rollback_executed'] ?? null) !== false, 'Smoke must not execute rollback.');
        fail_if($successDefinition->fresh()->status === 'published', 'BuilderDefinition must remain unpublished.');
        fail_if(($smokeReport['summary']['php_syntax_passed'] ?? 0) < 2, 'Smoke must syntax-check generated PHP and migration files.');
        fail_if(($smokeReport['summary']['json_valid'] ?? 0) < 1, 'Smoke must validate generated JSON.');
        fail_if(($smokeReport['summary']['migration_files_not_executed'] ?? 0) < 1, 'Smoke must identify generated migration files as not executed.');
        foreach ($smokeReport['files'] ?? [] as $file) {
            fail_if(($file['exists'] ?? false) !== true, 'Smoke reported missing committed file.');
            fail_if(($file['hash_matches'] ?? false) !== true, 'Smoke reported hash mismatch in clean case.');
            fail_if(($file['syntax_checked'] ?? false) === true && ($file['syntax_valid'] ?? false) !== true, 'Generated PHP syntax must pass.');
            fail_if(($file['json_checked'] ?? false) === true && ($file['json_valid'] ?? false) !== true, 'Generated JSON must be valid.');
            fail_if(($file['rollback_manifest_entry_exists'] ?? false) !== true, 'Rollback entry must exist for every committed file.');
            fail_if(($file['backup_reference_exists_when_required'] ?? false) !== true, 'Overwrite backup evidence must remain available.');
            if (($file['migration_file'] ?? false) === true && Schema::hasTable('migrations')) {
                $migrationName = pathinfo((string) $file['runtime_path'], PATHINFO_FILENAME);
                fail_if(DB::table('migrations')->where('migration', $migrationName)->exists(), "Generated migration was executed: {$migrationName}");
            }
        }
        foreach ($beforeHashes as $path => $hash) {
            fail_if(hash_file('sha256', project_path($path)) !== $hash, "Smoke modified runtime file: {$path}");
        }

        [$tamperDefinition, $tamperExecution, $tamperWriteReport] = execute_full_chain('BuilderSmokeTamper'.$suffix);
        $tamperedPath = (string) ($tamperWriteReport['files_committed'][0]['future_runtime_path'] ?? '');
        File::append(project_path($tamperedPath), "\n// tampered after guarded runtime write\n");
        $tamperSmoke = app(BuilderRuntimeWritePostWriteSmokeService::class)->verify($tamperExecution->fresh());
        fail_if(($tamperSmoke['status'] ?? null) !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_FAILED, 'Tampered committed file must fail smoke.');
        fail_if(($tamperSmoke['post_write_smoke_passed'] ?? null) !== false, 'Tampered smoke must not pass.');

        [$forbiddenDefinition, $forbiddenExecution] = execute_full_chain('BuilderSmokeForbidden'.$suffix);
        $forbiddenExecution = $forbiddenExecution->fresh();
        $metadata = $forbiddenExecution->metadata_json ?: [];
        $runtimeReportPath = (string) $metadata['runtime_write_report_path'];
        $forgedReport = json_decode((string) file_get_contents(project_path($runtimeReportPath)), true);
        $forgedReport['files_committed'][] = [
            'future_runtime_path' => 'modules/Core/App/Models/ForbiddenSmoke.php',
            'committed_sha256' => hash('sha256', 'forbidden'),
            'write_action' => 'create',
        ];
        File::put(project_path($runtimeReportPath), json_encode($forgedReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $forbiddenSmoke = app(BuilderRuntimeWritePostWriteSmokeService::class)->verify($forbiddenExecution->fresh());
        fail_if(($forbiddenSmoke['status'] ?? null) !== BuilderPublishExecution::STATUS_RUNTIME_WRITE_SMOKE_BLOCKED, 'Forbidden committed path report must block smoke.');
        fail_if(is_file(project_path('modules/Core/App/Models/ForbiddenSmoke.php')), 'Forbidden path test must not create a Core file.');

        fail_if(! BuilderPublishAuditLog::where('event_type', 'runtime_write_smoke_passed')->where('builder_definition_id', $successDefinition->getKey())->exists(), 'runtime_write_smoke_passed audit event missing.');
        fail_if(! BuilderPublishAuditLog::where('event_type', 'runtime_write_smoke_failed')->where('builder_definition_id', $tamperDefinition->getKey())->exists(), 'runtime_write_smoke_failed audit event missing.');
        fail_if(! BuilderPublishAuditLog::where('event_type', 'runtime_write_smoke_blocked')->where('builder_definition_id', $forbiddenDefinition->getKey())->exists(), 'runtime_write_smoke_blocked audit event missing.');
    } catch (Throwable $exception) {
        $errors[] = 'Runtime verifier failed: '.$exception->getMessage();
    }
}

foreach (array_unique($cleanupPaths) as $path) {
    if (str_starts_with($path, 'modules/BuilderSmoke') || str_starts_with($path, 'storage/app/builder-')) {
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
echo "Builder runtime write post-write smoke MVP verified. Committed runtime files were validated without publish, migration execution, route registration, mark-published, rollback, or runtime file modification by the smoke service.\n";
