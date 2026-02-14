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

namespace Modules\Updater;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use JsonSerializable;
use Modules\Core\Models\Patch as PatchModel;

final class Patch implements Arrayable, JsonSerializable
{
    use DownloadsFiles;

    /**
     * @var \Modules\Updater\ZipArchive
     */
    protected $archive;

    /**
     * Initialize new Relase instance.
     */
    public function __construct(protected object $patch) {}

    /**
     * Check whether the patch is applied.
     */
    public function isApplied(): bool
    {
        return ! is_null(PatchModel::where('token', $this->token())->first());
    }

    /**
     * Mark patch as applied.
     */
    public function markAsApplied(): bool
    {
        (new PatchModel([
            'token' => $this->token(),
            'version' => $this->version(),
        ]))->save();

        return true;
    }

    /**
     * Get the patch token.
     */
    public function token(): string
    {
        return $this->patch->token;
    }

    /**
     * Check if the patch is critical.
     */
    public function isCritical(): bool
    {
        if (filter_var($this->patch->critical, FILTER_VALIDATE_BOOL)) {
            return $this->patch->critical;
        }

        return false;
    }

    /**
     * Get the patch description.
     */
    public function description(): string
    {
        return $this->patch->description;
    }

    /**
     * Get the patch date.
     */
    public function date(): Carbon
    {
        return $this->patch->date;
    }

    /**
     * Get the patch version.
     */
    public function version(): string
    {
        return $this->patch->version;
    }

    /**
     * Get the release archive.
     */
    public function archive(): ZipArchive
    {
        return $this->archive ??= new ZipArchive($this->getStoragePath());
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'description' => $this->description(),
            'date' => $this->date()->toJSON(),
            'token' => $this->token(),
            'isCritical' => $this->isCritical(),
            'isApplied' => $this->isApplied(),
        ];
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
