<?php

declare(strict_types=1);

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

foreach ([
    'docs/ai/03-architecture/builder-runtime-write-execution-architecture-review.md',
    'docs/ai/03-architecture/builder-runtime-write-operator-runbook.md',
    'docs/ai/03-architecture/builder-runtime-write-kill-switch-policy.md',
    'docs/ai/03-architecture/builder-runtime-write-failure-and-rollback-policy.md',
] as $path) {
    fail_if(! is_file(project_path($path)), "Missing architecture doc: {$path}");
}

$review = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-execution-architecture-review-contract.json');
$runbook = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-operator-runbook-contract.json');
$killSwitch = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-kill-switch-contract.json');
$failure = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-failure-policy-contract.json');
$phase = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-phase-contract.json');
$readiness = json_contract('docs/ai/05-rag/contracts/builder-post-backup-runtime-write-readiness-contract.json');
$rollback = json_contract('docs/ai/05-rag/contracts/builder-publish-rollback-manifest-contract.json');
$safety = json_contract('docs/ai/05-rag/contracts/builder-publish-safety-contract.json');
$audit = json_contract('docs/ai/05-rag/contracts/builder-publish-audit-log-contract.json');
$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$tools = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');
$mcp = json_contract('docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');

fail_if(($review['current_implementation_status'] ?? null) !== 'review_only', 'Architecture review contract must be review_only.');
foreach ([
    'runtime_write_endpoint_implemented',
    'copy_to_runtime_endpoint_implemented',
    'publish_endpoint_implemented',
    'rollback_endpoint_implemented',
    'migrations_run_in_runtime_write_phase',
    'routes_registered_in_runtime_write_phase',
    'mark_published_in_runtime_write_phase',
    'ai_may_execute_runtime_write',
    'mcp_may_execute_runtime_write',
] as $key) {
    fail_if(($review[$key] ?? null) !== false, "Architecture review {$key} must be false.");
}
fail_if(($review['required_execution_status'] ?? null) !== 'runtime_write_readiness_passed', 'Future execution must require runtime_write_readiness_passed.');
foreach ([
    'requires_runtime_write_lock',
    'requires_kill_switch_enabled',
    'requires_operator_runbook_acknowledgement',
    'requires_backup_manifest',
    'requires_rollback_manifest',
    'post_write_smoke_required_after_runtime_write',
] as $key) {
    fail_if(($review[$key] ?? null) !== true, "Architecture review {$key} must be true.");
}

fail_if(($runbook['current_implementation_status'] ?? null) !== 'runbook_only', 'Operator runbook contract must be runbook_only.');
fail_if(($runbook['operator_acknowledgement_required_before_future_runtime_write'] ?? null) !== true, 'Operator acknowledgement must be required.');
fail_if(($runbook['ai_may_acknowledge_operator_runbook'] ?? null) !== false, 'AI must not acknowledge operator runbook.');
fail_if(($runbook['mcp_may_acknowledge_operator_runbook'] ?? null) !== false, 'MCP must not acknowledge operator runbook.');

fail_if(($killSwitch['current_implementation_status'] ?? null) !== 'policy_only', 'Kill-switch contract must be policy_only.');
fail_if(($killSwitch['runtime_write_enabled_default'] ?? null) !== false, 'Runtime write kill-switch default must be false.');
fail_if(($killSwitch['environment_guard_required'] ?? null) !== true, 'Environment guard must be required.');
fail_if(($killSwitch['config_guard_required'] ?? null) !== true, 'Config guard must be required.');
fail_if(($killSwitch['per_execution_guard_required'] ?? null) !== true, 'Per-execution guard must be required.');
fail_if(($killSwitch['ai_may_override_kill_switch'] ?? null) !== false, 'AI must not override kill-switch.');
fail_if(($killSwitch['mcp_may_override_kill_switch'] ?? null) !== false, 'MCP must not override kill-switch.');

fail_if(($failure['rollback_execution_implemented'] ?? null) !== false, 'Rollback execution must remain unimplemented.');
fail_if(($failure['automatic_rollback_implemented'] ?? null) !== false, 'Automatic rollback must remain unimplemented.');
fail_if(($failure['human_confirmation_required_for_future_rollback'] ?? null) !== true, 'Future rollback must require human confirmation.');
fail_if(($failure['partial_write_detection_required'] ?? null) !== true, 'Partial write detection must be required.');
fail_if(($failure['checksum_verification_required'] ?? null) !== true, 'Checksum verification must be required.');

fail_if(($phase['runtime_write_execution_architecture_review_completed'] ?? null) !== true, 'Runtime write phase contract must mention architecture review completed.');
fail_if(($phase['runtime_write_still_forbidden'] ?? null) !== true, 'Runtime write must remain forbidden.');
fail_if(($phase['kill_switch_required_for_future_runtime_write'] ?? null) !== true, 'Kill-switch must be required for future runtime write.');
fail_if(($phase['operator_runbook_required_for_future_runtime_write'] ?? null) !== true, 'Operator runbook must be required for future runtime write.');
fail_if(($readiness['future_execution_still_requires_architecture_review'] ?? null) !== true, 'Readiness contract must require architecture review.');
fail_if(($readiness['future_execution_requires_separate_implementation'] ?? null) !== true, 'Readiness contract must require separate implementation.');
fail_if(($rollback['rollback_execution_implemented'] ?? null) !== false, 'Rollback execution must remain not implemented.');
fail_if(($rollback['future_rollback_requires_separate_human_confirmation'] ?? null) !== true, 'Future rollback must require human confirmation.');

$encodedSafety = json_encode($safety, JSON_PRETTY_PRINT) ?: '';
$encodedAudit = json_encode($audit, JSON_PRETTY_PRINT) ?: '';
$encodedManifest = json_encode($manifest, JSON_PRETTY_PRINT) ?: '';
$encodedBoundaries = json_encode($boundaries, JSON_PRETTY_PRINT) ?: '';
$encodedTools = json_encode($tools, JSON_PRETTY_PRINT) ?: '';
$encodedMcp = json_encode($mcp, JSON_PRETTY_PRINT) ?: '';

foreach ([
    'runtime write execution architecture review',
    'actual_runtime_write_still_forbidden'
] as $needle) {
    fail_if(! str_contains($encodedSafety, $needle), "Publish safety contract missing {$needle}.");
}
foreach ([
    'runtime_write_architecture_reviewed',
    'runtime_write_operator_acknowledged',
    'runtime_write_kill_switch_checked',
    'runtime_write_file_temp_created',
    'runtime_write_file_committed',
    'runtime_write_file_failed',
    'runtime_write_aborted',
] as $event) {
    fail_if(! str_contains($encodedAudit, $event), "Audit contract missing future event {$event}.");
}
fail_if(! str_contains($encodedManifest, 'runtime write execution architecture review'), 'RAG manifest must mention runtime write execution architecture review.');
foreach ([
    'acknowledge runtime write operator runbook',
    'override runtime write kill-switch',
    'execute runtime write',
    'execute rollback',
    'mark module published',
] as $needle) {
    fail_if(! str_contains($encodedBoundaries, $needle), "Safety boundaries missing {$needle}.");
}
foreach ([
    'runtime_write_execution_tool_implemented',
    'operator_acknowledgement_tool_implemented',
    'kill_switch_override_tool_implemented',
    'rollback_execution_tool_implemented',
] as $key) {
    fail_if(($tools[$key] ?? null) !== false, "Tool Registry {$key} must be false.");
}
foreach ([
    'mcp_must_not_expose_runtime_write_execution_tools',
    'mcp_must_not_expose_kill_switch_override_tools',
    'mcp_must_not_expose_operator_acknowledgement_tools',
    'mcp_must_not_expose_rollback_execution_tools',
] as $key) {
    fail_if(($mcp[$key] ?? null) !== true, "MCP contract {$key} must be true.");
}

$routes = read_project_file('routes/api.php');
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write endpoint exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime endpoint exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish endpoint exists.');
fail_if(str_contains($routes, 'rollback-executions'), 'Forbidden rollback endpoint exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$builderApi = read_project_file('modules/Builder/resources/js/services/builderApi.js');
foreach (['executeRuntimeWrite', 'copyToRuntime', 'publishDefinition', 'executePublish', 'rollbackPublish'] as $forbidden) {
    fail_if(str_contains($builderApi, $forbidden), "Forbidden builderApi method exists: {$forbidden}");
}

$ui = read_project_file('modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue')
    .read_project_file('modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue')
    .read_project_file('modules/Builder/resources/js/views/BuilderDefinitionView.vue');
foreach (['Execute Runtime Write', 'Copy to Runtime', 'text="Publish"', 'Execute Publish', 'Deploy', 'text="Rollback"', 'Run migrations'] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI text exists: {$forbidden}");
}

foreach ([
    'database/migrations',
    'modules/Warehouse',
    'modules/Core',
    'modules/SaaS',
    'modules/Updater',
    'modules/Installer',
    'package.json',
    'composer.json',
    'public/build',
    'app/Console/Commands',
] as $path) {
    $status = [];
    exec('cd '.escapeshellarg($root).' && git -c safe.directory='.escapeshellarg($root).' --no-pager status --short -- '.escapeshellarg($path), $status);
    fail_if($status !== [], "Forbidden path has changes: {$path} ".implode('; ', $status));
}

foreach ([
    'app/Services/Builder/BuilderRuntimeWriteExecutionService.php',
    'app/Services/Builder/BuilderRuntimeWriteCopyService.php',
    'app/Http/Controllers/Builder/BuilderRuntimeWriteExecutionController.php',
    'app/Http/Controllers/Builder/BuilderRollbackExecutionController.php',
] as $path) {
    fail_if(is_file(project_path($path)), "Forbidden implementation file exists: {$path}");
}

if ($errors !== []) {
    echo "FAIL\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "PASS\n";
echo "Builder runtime write execution architecture review verified. Runtime write, publish, copy-to-runtime, and rollback remain unimplemented.\n";
