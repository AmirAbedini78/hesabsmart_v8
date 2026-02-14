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

namespace Modules\Core\Macros;

class DeepCleanDirectory
{
    /**
     * Deep clean the given directory
     *
     * @param  string  $directory
     */
    public function __invoke($directory, bool $preserve = true, array $except = []): bool
    {
        if (! is_dir($directory)) {
            return false;
        }

        $totalDeleted = 0;

        if (substr($directory, strlen($directory) - 1, 1) != '/') {
            $directory .= '/';
        }

        $items = glob($directory.'*', GLOB_MARK);

        foreach ($items as $item) {
            if (is_dir($item)) {
                if ((new static)($item, false)) {
                    $totalDeleted++;
                }
            } elseif (! in_array($item, $except)) {
                if (unlink($item)) {
                    $totalDeleted++;
                }
            }
        }

        if (! $preserve) {
            @rmdir($directory);
        }

        return $totalDeleted > 0;
    }
}
