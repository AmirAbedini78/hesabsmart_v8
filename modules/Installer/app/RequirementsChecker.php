<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Installer;

class RequirementsChecker
{
    protected array $requirements;

    protected string $minPhpVersion;

    /**
     * Initialize new RequirementsChecker instance.
     */
    public function __construct(?array $requirements = null, ?string $minPhpVersion = null)
    {
        $this->requirements = $requirements ?? config('installer.requirements');
        $this->minPhpVersion = $minPhpVersion ?? config('installer.core.minPhpVersion');
    }

    /**
     * Check the installer requirements.
     */
    public function check(): array
    {
        $results = $this->createEmptyResultSet();

        $requirements = $this->requirements;

        foreach ($requirements as $type => $requirement) {
            switch ($type) {
                case 'php':
                    $checks = $this->checkPHPRequirements($requirements[$type]);

                    $results['results'][$type] = array_merge($results['results'][$type], $checks);

                    if ($this->determineIfFails($checks)) {
                        $results['errors'] = true;
                    }

                    break;

                case 'functions':
                    $checks = $this->checkPHPFunctions($requirements[$type]);

                    $results['results'][$type] = array_merge($results['results'][$type], $checks);

                    if ($this->determineIfFails($checks)) {
                        $results['errors'] = true;
                    }

                    break;

                case 'apache':
                    foreach ($requirements[$type] as $requirement) {
                        // if function doesn't exist we can't check apache modules
                        if (function_exists('apache_get_modules')) {
                            $results['results'][$type][$requirement] = true;

                            if (! in_array($requirement, apache_get_modules())) {
                                $results['results'][$type][$requirement] = false;

                                $results['errors'] = true;
                            }
                        }
                    }

                    break;
                case 'recommended':
                    $results['recommended']['php'] = $this->checkPHPRequirements($requirements[$type]['php']);
                    $results['recommended']['functions'] = $this->checkPHPFunctions($requirements[$type]['functions']);

                    break;
            }
        }

        return $results;
    }

    /**
     * Check whether the given PHP requirement passes.
     */
    public function passes(string $requirement): bool
    {
        $requirements = $this->check();

        if (! array_key_exists($requirement, $requirements['recommended']['php'])) {
            return $requirements['results']['php'][$requirement] ?? true;
        }

        return $requirements['recommended']['php'][$requirement];
    }

    /**
     * Check whether the given PHP requirement fails.
     */
    public function fails(string $requirement): bool
    {
        return ! $this->passes($requirement);
    }

    /**
     * Check the php requirements.
     */
    protected function checkPHPRequirements(array $requirements): array
    {
        $results = [];

        foreach ($requirements as $requirement) {
            $results[$requirement] = $this->extensionLoaded($requirement);
        }

        return $results;
    }

    /**
     * Check the PHP functions requirements.
     */
    protected function checkPHPFunctions(array $functions): array
    {
        $results = [];

        foreach ($functions as $function) {
            $results[$function] = $this->functionExists($function);
        }

        return $results;
    }

    /**
     * Determine if all checks fails.
     */
    protected function determineIfFails(array $checks): bool
    {
        return count(array_filter($checks)) !== count($checks);
    }

    /**
     * Check PHP version requirement.
     */
    public function checkPHPversion(): array
    {
        $currentPhpVersion = static::getPhpVersionInfo();

        return [
            'full' => $currentPhpVersion['full'],
            'current' => $currentPhpVersion['version'],
            'minimum' => $this->minPhpVersion,
            'supported' => $this->isSupportedPHPVersion($currentPhpVersion['version']),
        ];
    }

    /**
     * Check whether the given extension is loaded.
     *
     * @codeCoverageIgnore
     */
    protected function extensionLoaded(string $extension): bool
    {
        return extension_loaded($extension);
    }

    /**
     * Check whether the given function exists.
     *
     * @codeCoverageIgnore
     */
    protected function functionExists(string $function): bool
    {
        return in_array($function, get_defined_functions()['internal']);
    }

    /**
     * Check whether the PHP version is supported.
     */
    protected function isSupportedPHPVersion(string $currentPhpVersion): bool
    {
        return version_compare($currentPhpVersion, $this->minPhpVersion, '>=');
    }

    /**
     * Get current Php version information.
     */
    protected static function getPhpVersionInfo(): array
    {
        $currentVersionFull = PHP_VERSION;
        preg_match("#^\d+(\.\d+)*#", $currentVersionFull, $filtered);
        $currentVersion = $filtered[0];

        return [
            'full' => $currentVersionFull,
            'version' => $currentVersion,
        ];
    }

    /**
     * Create empty result set.
     */
    protected function createEmptyResultSet(): array
    {
        return [
            'results' => [
                'php' => [],
                'functions' => [],
                'apache' => [],
            ],
            'recommended' => [
                'php' => [],
            ],
            'errors' => false,
        ];
    }
}
