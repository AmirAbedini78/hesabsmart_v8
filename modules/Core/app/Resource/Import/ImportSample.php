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

namespace Modules\Core\Resource\Import;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Contracts\Fields\Dateable;
use Modules\Core\Fields\Field;
use Modules\Core\Resource\Resource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportSample implements FromArray
{
    /**
     * Create new Import instance.
     */
    public function __construct(protected Resource $resource, protected readonly int $totalRows = 1) {}

    /**
     * Download sample
     */
    public function download(): BinaryFileResponse
    {
        return Excel::download($this, 'sample.csv');
    }

    /**
     * Creates the sample data rows
     */
    public function array(): array
    {
        $data = [
            $this->getHeadings(),
        ];

        for ($i = 1; $i <= $this->totalRows; $i++) {
            $data[] = $this->generateRow();
        }

        return $data;
    }

    /**
     * Get sample headings by fields
     */
    public function getHeadings(): array
    {
        return $this->resource->fieldsForImportSample()
            ->map(function (Field $field) {
                if ($field instanceof Dateable) {
                    return $field->label.' ('.config('app.timezone').')';
                }

                return $field->label;
            })->all();
    }

    /**
     * Prepares import sample row
     */
    public function generateRow(): array
    {
        return $this->resource->fieldsForImportSample()
            ->reduce(function ($carry, $field) {
                $carry[] = $field->sampleValueForImport();

                return $carry;
            }, []);
    }
}
