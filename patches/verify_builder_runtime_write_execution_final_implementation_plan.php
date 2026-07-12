<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];

function fail(array &$failures, string $message): void
{
    $failures[] = $message;
}

function assertTrue(array &$failures, bool $condition, string $message): void
{
    if (! $condition) {
        fail($failures, $message);
    }
}

function readJson(array &$failures, string $path): array
{
    global $root;

    $fullPath = $root.'/'.$path;
    if (! is_file($fullPath)) {
        fail($failures, "Missing JSON file: {$path}");

        return [];
    }

    $decoded = json_decode((string) file_get_contents($fullPath), true);
    if (! is_array($decoded)) {
        fail($failures, "Invalid JSON file: {$path} (".json_last_error_msg().')');

        return [];
    }

    return $decoded;
}

function fileContains(string $path, string $needle): bool
{
    global $root;

    $fullPath = $root.'/'.$path;

    return is_file($fullPath) && str_contains((string) file_get_contents($fullPath), $needle);
}

function treeContains(string $relativeDir, array $needles): array
{
    global $root;

    $matches = [];
    $dir = $root.'/'.$relativeDir;
    if (! is_dir($dir)) {
        return $matches;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());
        foreach ($needles as $needle) {
            if (str_contains($contents, $needle)) {
                $matches[] = str_replace($root.'/', '', $file->getPathname())." contains {$needle}";
            }
        }
    }

    return $matches;
}

$docs = [
    'docs/ai/03-architecture/builder-runtime-write-execution-final-implementation-plan.md',
    'docs/ai/03-architecture/builder-runtime-write-execution-verifier-strategy.md',
    'docs/ai/03-architecture/builder-runtime-write-ui-safety-requirements.md',
];

foreach ($docs as $doc) {
    assertTrue($failures, is_file($root.'/'.$doc), "Missing architecture doc: {$doc}");
}

$finalContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-runtime-write-execution-final-implementation-contract.json');
$verifierContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-runtime-write-execution-verifier-contract.json');
$uiContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-runtime-write-ui-safety-contract.json');
$phaseContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-runtime-write-phase-contract.json');
$operatorContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-runtime-write-operator-acknowledgement-contract.json');
$guardContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-runtime-write-kill-switch-guard-contract.json');
$readinessContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-post-backup-runtime-write-readiness-contract.json');
$rollbackContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-publish-rollback-manifest-contract.json');
$safetyContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-publish-safety-contract.json');
$auditContract = readJson($failures, 'docs/ai/05-rag/contracts/builder-publish-audit-log-contract.json');
$manifest = readJson($failures, 'docs/ai/05-rag/contracts/builder-studio-ai-rag-manifest.json');
$agentBoundaries = readJson($failures, 'docs/ai/05-rag/contracts/builder-agent-safety-boundaries.json');
$toolRegistry = readJson($failures, 'docs/ai/05-rag/contracts/ai-tool-registry-contract.json');
$mcpContract = readJson($failures, 'docs/ai/05-rag/contracts/mcp-adapter-future-contract.json');

assertTrue($failures, ($finalContract['current_implementation_status'] ?? null) === 'final_implementation_plan_only', 'Final implementation contract must be plan-only.');
assertTrue($failures, ($finalContract['runtime_write_endpoint_implemented'] ?? true) === false, 'Runtime write endpoint must not be implemented.');
assertTrue($failures, ($finalContract['runtime_write_service_implemented'] ?? true) === false, 'Runtime write service must not be implemented.');
assertTrue($failures, ($finalContract['runtime_write_ui_action_implemented'] ?? true) === false, 'Runtime write UI action must not be implemented.');
assertTrue($failures, ($finalContract['future_endpoint'] ?? null) === 'POST /api/builder/publish-executions/{id}/execute-runtime-write', 'Future execute-runtime-write endpoint must be documented.');
assertTrue($failures, ($finalContract['required_execution_status'] ?? null) === 'runtime_write_operator_acknowledged', 'Required future execution status must be runtime_write_operator_acknowledged.');
assertTrue($failures, ($finalContract['migrations_run_in_runtime_write_phase'] ?? true) === false, 'Migrations must not run in runtime write phase.');
assertTrue($failures, ($finalContract['routes_registered_in_runtime_write_phase'] ?? true) === false, 'Routes must not be registered in runtime write phase.');
assertTrue($failures, ($finalContract['mark_published_in_runtime_write_phase'] ?? true) === false, 'Runtime write phase must not mark published.');
assertTrue($failures, ($finalContract['rollback_execution_in_runtime_write_phase'] ?? true) === false, 'Runtime write phase must not execute rollback.');
assertTrue($failures, ($finalContract['ai_may_execute_runtime_write'] ?? true) === false, 'AI must not execute runtime write.');
assertTrue($failures, ($finalContract['mcp_may_execute_runtime_write'] ?? true) === false, 'MCP must not execute runtime write.');

assertTrue($failures, ($verifierContract['current_implementation_status'] ?? null) === 'verifier_strategy_only', 'Verifier contract must be strategy-only.');
assertTrue($failures, ($verifierContract['future_verifier_required'] ?? false) === true, 'Future verifier must be required.');
assertTrue($failures, ($uiContract['current_implementation_status'] ?? null) === 'ui_safety_requirements_only', 'UI safety contract must be requirements-only.');
assertTrue($failures, ($uiContract['runtime_write_ui_action_implemented'] ?? true) === false, 'UI safety contract must say runtime write UI action is not implemented.');
assertTrue($failures, ($uiContract['future_button_label'] ?? null) === 'Execute Runtime Write', 'Future UI button label must be documented.');
assertTrue($failures, ($uiContract['destructive_confirmation_required'] ?? false) === true, 'Destructive confirmation must be required.');
assertTrue($failures, ($uiContract['typed_confirmation_required'] ?? false) === true, 'Typed confirmation must be required.');
assertTrue($failures, ($uiContract['ai_may_click_runtime_write'] ?? true) === false, 'AI must not click runtime write.');
assertTrue($failures, ($uiContract['mcp_may_click_runtime_write'] ?? true) === false, 'MCP must not click runtime write.');

assertTrue($failures, ($phaseContract['runtime_write_execution_final_implementation_plan_completed'] ?? false) === true, 'Runtime write phase contract must mention final implementation plan completion.');
assertTrue($failures, ($phaseContract['runtime_write_endpoint_implemented'] ?? true) === false, 'Runtime write phase contract must keep endpoint unimplemented.');
assertTrue($failures, ($phaseContract['runtime_write_ui_action_implemented'] ?? true) === false, 'Runtime write phase contract must keep UI action unimplemented.');
assertTrue($failures, ($phaseContract['runtime_write_still_forbidden'] ?? false) === true, 'Runtime write phase contract must keep runtime write forbidden.');
assertTrue($failures, ($operatorContract['future_runtime_write_requires_acknowledged_operator_runbook'] ?? false) === true, 'Operator acknowledgement contract must gate future runtime write.');
assertTrue($failures, ($operatorContract['acknowledgement_itself_is_not_execution'] ?? false) === true, 'Operator acknowledgement must not be execution.');
assertTrue($failures, ($guardContract['future_runtime_write_requires_guard_passed'] ?? false) === true, 'Kill-switch guard contract must gate future runtime write.');
assertTrue($failures, ($guardContract['runtime_write_guard_passed_is_not_execution'] ?? false) === true, 'Guard passed must not be execution.');
assertTrue($failures, ($readinessContract['future_runtime_write_requires_readiness_passed'] ?? false) === true, 'Post-backup readiness must gate future runtime write.');
assertTrue($failures, ($readinessContract['readiness_passed_is_not_execution'] ?? false) === true, 'Readiness passed must not be execution.');
assertTrue($failures, ($rollbackContract['future_runtime_write_execution_may_update_committed_file_entries'] ?? false) === true, 'Rollback manifest contract must mention future committed file entries.');
assertTrue($failures, ($rollbackContract['rollback_execution_still_separate_and_not_implemented'] ?? false) === true, 'Rollback execution must remain separate and unimplemented.');

assertTrue($failures, str_contains(json_encode($safetyContract), 'runtime write execution final implementation plan'), 'Publish safety contract must mention final implementation plan.');
assertTrue($failures, str_contains(json_encode($safetyContract), 'actual_runtime_write_still_forbidden'), 'Publish safety contract must keep actual runtime write forbidden.');

$auditJson = json_encode($auditContract);
foreach ([
    'runtime_write_execute_requested',
    'runtime_write_started',
    'runtime_write_file_temp_created',
    'runtime_write_file_hash_verified',
    'runtime_write_file_committed',
    'runtime_write_succeeded',
    'runtime_write_failed',
    'runtime_write_aborted',
] as $event) {
    assertTrue($failures, str_contains($auditJson, $event), "Audit contract missing future event: {$event}");
}

$manifestJson = json_encode($manifest);
assertTrue($failures, str_contains($manifestJson, 'builder-runtime-write-execution-final-implementation-plan.md'), 'RAG manifest must mention final implementation plan doc.');
assertTrue($failures, str_contains($manifestJson, 'builder-runtime-write-execution-final-implementation-contract.json'), 'RAG manifest must mention final implementation contract.');
assertTrue($failures, str_contains($manifestJson, 'plan-only'), 'RAG manifest must clarify plan-only status.');

$agentJson = json_encode($agentBoundaries);
assertTrue($failures, str_contains($agentJson, 'summarize runtime write execution final implementation plan'), 'Agent boundaries must allow summarizing final implementation plan.');
assertTrue($failures, ($agentBoundaries['lifecycle_planning_boundaries']['ai_builder_agent_may_execute_runtime_write'] ?? true) === false, 'Agent boundaries must forbid runtime write execution.');
assertTrue($failures, ($agentBoundaries['lifecycle_planning_boundaries']['ai_builder_agent_may_click_future_runtime_write_button'] ?? true) === false, 'Agent boundaries must forbid clicking future runtime write button.');
assertTrue($failures, ($agentBoundaries['lifecycle_planning_boundaries']['ai_builder_agent_may_use_mcp_for_runtime_write'] ?? true) === false, 'Agent boundaries must forbid MCP runtime write.');
assertTrue($failures, ($agentBoundaries['lifecycle_planning_boundaries']['ai_builder_agent_may_mark_published_after_runtime_write'] ?? true) === false, 'Agent boundaries must forbid marking published.');
assertTrue($failures, ($agentBoundaries['lifecycle_planning_boundaries']['ai_builder_agent_may_run_migrations_for_runtime_write'] ?? true) === false, 'Agent boundaries must forbid running migrations.');

$toolNames = array_map(static fn (array $tool): string => $tool['name'] ?? '', $toolRegistry['initial_builder_tools'] ?? []);
assertTrue($failures, ($toolRegistry['execute_runtime_write_tool_implemented'] ?? true) === false, 'Tool Registry must say execute runtime write tool is not implemented.');
assertTrue($failures, ($toolRegistry['future_execute_runtime_write_tool_available'] ?? true) === false, 'Tool Registry must say future execute tool is unavailable.');
assertTrue($failures, ($toolRegistry['publish_tool_implemented'] ?? true) === false, 'Tool Registry must say no publish tool exists.');
assertTrue($failures, ($toolRegistry['migration_execution_tool_implemented'] ?? true) === false, 'Tool Registry must say no migration execution tool exists.');
assertTrue($failures, ! in_array('builder.execute_runtime_write', $toolNames, true), 'Tool Registry must not expose builder.execute_runtime_write.');

assertTrue($failures, ($mcpContract['mcp_must_not_expose_execute_runtime_write_tool_current_mvp'] ?? false) === true, 'MCP contract must forbid execute-runtime-write tool.');
assertTrue($failures, ($mcpContract['mcp_must_not_expose_publish_tools'] ?? false) === true, 'MCP contract must forbid publish tools.');
assertTrue($failures, ($mcpContract['mcp_must_not_expose_migration_execution_tools'] ?? false) === true, 'MCP contract must forbid migration tools.');
assertTrue($failures, ($mcpContract['mcp_must_not_expose_rollback_execution_tools'] ?? false) === true, 'MCP contract must forbid rollback tools.');

assertTrue($failures, ! is_file($root.'/app/Services/Builder/BuilderRuntimeWriteExecutionService.php'), 'Runtime write execution service must not exist yet.');

$routeText = (string) file_get_contents($root.'/routes/api.php');
foreach (['execute-runtime-write', 'copy-to-runtime', 'execute-publish'] as $forbiddenRouteFragment) {
    assertTrue($failures, ! str_contains($routeText, $forbiddenRouteFragment), "Forbidden route fragment exists: {$forbiddenRouteFragment}");
}
assertTrue($failures, ! preg_match("/Route::post\\([^\\n]*['\\\"](?:definitions\\/\\{builderDefinition\\}\\/publish|publish)['\\\"]/", $routeText), 'Executable publish endpoint must not exist.');
assertTrue($failures, ! preg_match("/Route::post\\([^\\n]*rollback(?!-manifest)/", $routeText), 'Rollback endpoint must not exist.');

$controllerMatches = treeContains('app/Http/Controllers/Builder', ['executeRuntimeWrite']);
assertTrue($failures, $controllerMatches === [], 'Controller executeRuntimeWrite method must not exist: '.implode('; ', $controllerMatches));

$apiMatches = treeContains('modules/Builder/resources/js/services', [
    'executeRuntimeWrite',
    'copyToRuntime',
    'publishDefinition',
    'executePublish',
    'rollbackPublish',
]);
assertTrue($failures, $apiMatches === [], 'Builder API must not expose runtime write/copy/publish/rollback: '.implode('; ', $apiMatches));

$uiMatches = treeContains('modules/Builder/resources/js', [
    'Execute Runtime Write',
    'Copy to Runtime',
    'Execute Publish',
    'Override Kill-Switch',
    'Enable Runtime Write',
]);
assertTrue($failures, $uiMatches === [], 'Builder UI must not expose runtime write/copy/publish controls: '.implode('; ', $uiMatches));

if ($failures !== []) {
    echo "FAIL\n";
    foreach ($failures as $failure) {
        echo "- {$failure}\n";
    }
    exit(1);
}

echo "PASS\n";
echo "Runtime write execution final implementation plan is documented without implementing runtime write execution.\n";
