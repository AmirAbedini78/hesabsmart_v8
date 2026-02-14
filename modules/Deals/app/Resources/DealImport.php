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

namespace Modules\Deals\Resources;

use Maatwebsite\Excel\Row;
use Modules\Core\Resource\Import\Import;

class DealImport extends Import
{
    /**
     * Map the row keys with it's selected attributes.
     */
    protected function mapRow(Row $row): array
    {
        $pipelineId = request()->integer('pipeline_id');

        if ($pipelineId === 0) {
            throw new \LogicException('Pipeline ID must be provided.');
        }

        return array_merge(parent::mapRow($row), ['pipeline_id' => $pipelineId]);
    }
}
