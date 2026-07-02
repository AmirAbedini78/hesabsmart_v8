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

function contains_text(string $haystack, string $needle, string $label): void
{
    fail_if(! str_contains($haystack, $needle), "{$label} missing {$needle}");
}

foreach ([
    'docs/ai/03-architecture/builder-runtime-write-final-confirmation-gate.md',
    'docs/ai/03-architecture/builder-runtime-write-confirmation-invalidation-strategy.md',
    'docs/ai/05-rag/contracts/builder-runtime-write-final-confirmation-contract.json',
    'docs/ai/04-docops/history/2026-07-02-builder-runtime-write-final-confirmation-planning.md',
] as $path) {
    fail_if(! file_exists(project_path($path)), "Missing required file: {$path}");
}

$confirmation = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-final-confirmation-contract.json');
fail_if(($confirmation['current_implementation_status'] ?? null) !== 'planning_only', 'Final confirmation contract must be planning_only.');
fail_if(($confirmation['final_confirmation_persistence_implemented'] ?? null) !== false, 'Final confirmation persistence must be false.');
fail_if(($confirmation['final_confirmation_endpoint_implemented'] ?? null) !== false, 'Final confirmation endpoint must be false.');
fail_if(($confirmation['runtime_write_endpoint_implemented'] ?? null) !== false, 'Runtime write endpoint must be false.');
fail_if(($confirmation['required_before_runtime_write'] ?? null) !== true, 'Final confirmation must be required before runtime write.');
fail_if(($confirmation['explicit_human_action_required'] ?? null) !== true, 'Explicit human action must be required.');
fail_if(($confirmation['ai_may_confirm'] ?? null) !== false, 'AI may confirm must be false.');
fail_if(($confirmation['mcp_may_confirm'] ?? null) !== false, 'MCP may confirm must be false.');
fail_if(($confirmation['confirmation_does_not_publish'] ?? null) !== true, 'Confirmation must not publish.');
fail_if(($confirmation['confirmation_does_not_write_runtime'] ?? null) !== true, 'Confirmation must not write runtime.');

$binds = $confirmation['binds_to'] ?? [];
foreach ([
    'builder_definition_id',
    'builder_publish_execution_id',
    'runtime_write_plan_path',
    'definition_checksum',
    'candidate_id',
    'staged_validation_report_path',
    'approved_candidate_preflight_snapshot',
] as $field) {
    fail_if(! in_array($field, $binds, true), "Final confirmation contract missing bind field: {$field}");
}

$rules = implode("\n", $confirmation['invalidation_rules'] ?? []);
foreach ([
    'definition checksum changed',
    'runtime write plan regenerated',
    'staged validation regenerated',
    'execution status changed away from runtime_write_planned',
    'approval request revoked',
    'candidate snapshot changed',
    'blocker appears in plan',
    'plan path missing',
    'plan JSON invalid',
    'confirmation expired',
    'user permissions changed',
    'runtime path allowlist changed',
] as $rule) {
    contains_text($rules, $rule, 'Final confirmation invalidation rules');
}

$phase = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-phase-contract.json');
fail_if(($phase['final_confirmation_planning'] ?? null) !== true, 'Runtime write phase contract must mention final confirmation planning.');
fail_if(($phase['final_confirmation_persistence_implemented'] ?? null) !== false, 'Runtime write phase contract must say final confirmation persistence is not implemented.');
fail_if(($phase['runtime_write_still_forbidden'] ?? null) !== true, 'Runtime write phase contract must keep runtime write forbidden.');

$plan = json_contract('docs/ai/05-rag/contracts/builder-runtime-write-plan-artifact-contract.json');
fail_if(($plan['final_confirmation_required_after_plan_before_future_runtime_write'] ?? null) !== true, 'Plan artifact contract must require final confirmation before future runtime write.');

$safety = json_contract('docs/ai/05-rag/contracts/builder-publish-safety-contract.json');
contains_text(json_encode($safety, JSON_PRETTY_PRINT) ?: '', 'runtime write final confirmation planning', 'Publish safety contract');
contains_text(json_encode($safety, JSON_PRETTY_PRINT) ?: '', 'actual_runtime_write_still_forbidden', 'Publish safety contract');

$audit = json_contract('docs/ai/05-rag/contracts/builder-publish-audit-log-contract.json');
$auditJson = json_encode($audit, JSON_PRETTY_PRINT) ?: '';
foreach ([
    'runtime_write_confirmation_requested',
    'runtime_write_confirmation_granted',
    'runtime_write_confirmation_rejected',
    'runtime_write_confirmation_revoked',
    'runtime_write_confirmation_invalidated',
] as $event) {
    contains_text($auditJson, $event, 'Audit contract');
}

$manifest = json_contract('docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
contains_text(json_encode($manifest, JSON_PRETTY_PRINT) ?: '', 'Runtime write final confirmation planning', 'RAG manifest');

$boundaries = json_contract('docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$boundariesJson = json_encode($boundaries, JSON_PRETTY_PRINT) ?: '';
contains_text($boundariesJson, 'grant runtime write final confirmation', 'Safety boundaries');
contains_text($boundariesJson, 'use MCP to confirm runtime write', 'Safety boundaries');
contains_text($boundariesJson, 'ai_builder_agent_may_execute_runtime_write', 'Safety boundaries');

$toolRegistry = json_contract('docs/ai/05-rag/contracts/ai-tool-registry-contract.json');
fail_if(($toolRegistry['final_confirmation_tool_implemented'] ?? null) !== false, 'Tool Registry must not implement final confirmation tool.');
fail_if(($toolRegistry['no_tool_maps_to_final_confirmation_endpoint'] ?? null) !== true, 'Tool Registry must say no tool maps to final confirmation endpoint.');
contains_text(json_encode($toolRegistry, JSON_PRETTY_PRINT) ?: '', 'confirm_runtime_write', 'Tool Registry forbidden tools');

$mcp = json_contract('docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');
fail_if(($mcp['mcp_server_implemented'] ?? null) !== false, 'MCP server must not be implemented.');
fail_if(($mcp['mcp_must_not_bypass_human_confirmation'] ?? null) !== true, 'MCP must not bypass human confirmation.');
fail_if(($mcp['agent_may_confirm_runtime_write_via_mcp'] ?? null) !== false, 'Agent must not confirm runtime write via MCP.');

$routes = read_project_file('routes/api.php');
fail_if(str_contains($routes, 'final-confirmation'), 'Forbidden final confirmation route exists.');
fail_if(str_contains($routes, 'confirm-runtime-write'), 'Forbidden runtime write confirmation route exists.');
fail_if(str_contains($routes, 'copy-to-runtime'), 'Forbidden copy-to-runtime route exists.');
fail_if(str_contains($routes, 'execute-publish'), 'Forbidden execute-publish route exists.');
fail_if(str_contains($routes, 'execute-runtime-write'), 'Forbidden execute-runtime-write route exists.');
fail_if((bool) preg_match("#definitions/\\{builderDefinition\\}/publish['\"]#", $routes), 'Forbidden /publish endpoint exists.');

$ui = '';
foreach ([
    'modules/Builder/resources/js/components/BuilderPublishExecutionRecords.vue',
    'modules/Builder/resources/js/components/BuilderValidationPreviewPanel.vue',
    'modules/Builder/resources/js/views/BuilderDefinitionView.vue',
    'modules/Builder/resources/js/services/builderApi.js',
] as $path) {
    $ui .= read_project_file($path)."\n";
}

foreach ([
    'Final Confirmation',
    'Confirm Runtime Write',
    'Grant Runtime Write',
    'Execute Runtime Write',
    'Copy to runtime',
    'text="Publish"',
    'Execute Publish',
    'Deploy',
    'text="Rollback"',
] as $forbidden) {
    fail_if(str_contains($ui, $forbidden), "Forbidden UI/API implementation text exists: {$forbidden}");
}

foreach ([
    'app/Services/Builder/BuilderRuntimeWriteFinalConfirmationService.php',
    'app/Http/Controllers/Builder/BuilderRuntimeWriteFinalConfirmationController.php',
    'app/Models/BuilderRuntimeWriteFinalConfirmation.php',
] as $path) {
    fail_if(file_exists(project_path($path)), "Forbidden implementation file exists: {$path}");
}

if ($errors !== []) {
    echo "FAIL\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "PASS\n";
echo "Builder runtime write final confirmation planning verified. No confirmation endpoint, runtime write, copy-to-runtime, or publish implementation exists.\n";
