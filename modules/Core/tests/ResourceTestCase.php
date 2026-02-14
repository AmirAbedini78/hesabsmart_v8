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

namespace Modules\Core\Tests;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Modules\Core\Actions\Action;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\Boolean;
use Modules\Core\Fields\ColorSwatch;
use Modules\Core\Fields\Date;
use Modules\Core\Fields\DateTime;
use Modules\Core\Fields\Email;
use Modules\Core\Fields\FieldsCollection;
use Modules\Core\Fields\Number;
use Modules\Core\Fields\Numeric;
use Modules\Core\Fields\Timezone;
use Modules\Core\Fields\Url;
use Modules\Core\Http\Requests\ResourceRequest;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\Import;
use Modules\Core\Models\Model;
use Modules\Core\Resource\Resource;
use Modules\Core\Table\Column;
use Modules\Core\Tests\Concerns\TestsCustomFields;
use Modules\Core\Tests\Concerns\TestsImportAndExport;
use Tests\TestCase;

class ResourceTestCase extends TestCase
{
    use TestsCustomFields, TestsImportAndExport;

    protected $resourceName;

    protected function resource(): Resource
    {
        return Innoclapps::resourceByName($this->resourceName);
    }

    protected function tableName(): string
    {
        return $this->resource()->newModel()->getTable();
    }

    protected function factory(): Factory
    {
        return $this->resource()->newModel()->factory();
    }

    protected function endpoint(): string
    {
        return "/api/{$this->resourceName}";
    }

    protected function indexEndpoint(): string
    {
        return $this->endpoint();
    }

    protected function createEndpoint(): string
    {
        return $this->endpoint();
    }

    protected function updateEndpoint($record): string
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function showEndpoint($record): string
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function deleteEndpoint($record): string
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function forceDeleteEndpoint($record): string
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "/api/trashed/{$this->resourceName}/{$id}";
    }

    protected function runAction($action, $ids, $extra = []): TestResponse
    {
        if ($ids instanceof Model) {
            $ids = $ids->getKey();
        } elseif ($ids instanceof Collection) {
            $ids = $ids->modelKeys();
        }

        return $this->postJson($this->actionEndpoint($action), [
            'ids' => Arr::wrap($ids),
            ...$extra,
        ]);
    }

    protected function actionEndpoint($action): string
    {
        $uriKey = (is_string($action) ? $this->findAction($action) : $action)->uriKey();

        return "{$this->endpoint()}/actions/{$uriKey}/run";
    }

    protected function importUploadEndpoint(): string
    {
        return "/api/{$this->resourceName}/import/upload";
    }

    protected function importEndpoint($import): string
    {
        $id = is_int($import) ? $import : $import->getKey();

        return "/api/{$this->resourceName}/import/{$id}";
    }

    protected function tableEndpoint(): string
    {
        return "/api/{$this->resourceName}/table";
    }

    protected function performImportUpload($fakeFile): Import
    {
        $this->signIn();

        Storage::fake('local');

        $this->postJson($this->importUploadEndpoint(), [
            'file' => $fakeFile,
        ]);

        return Import::latest()->first();
    }

    protected function findAction($uriKey): ?Action
    {
        return collect($this->resource()->actions(app(ResourceRequest::class)))
            ->first(fn ($action) => $action->uriKey() == $uriKey);
    }

    protected function customFields(): FieldsCollection
    {
        return $this->resource()->getFields()->filterCustomFields();
    }

    protected function findCustomField($fieldId): ?CustomField
    {
        return $this->customFields()->first(function ($field) use ($fieldId) {
            return $field->customField->field_id === $fieldId;
        })->customField;
    }

    protected function customFieldsLabels(): array
    {
        return $this->customFields()->pluck('label')->all();
    }

    protected function assertThatDatabaseHasCustomFieldsValues(): void
    {
        $this->assertDatabaseHas($this->tableName(), [
            'cf_custom_field_boolean' => 1,
            'cf_custom_field_date' => '2021-12-05',
            'cf_custom_field_datetime' => '2021-12-05 10:00:00',
            'cf_custom_field_work_email' => 'info@concordcrm.com',
            'cf_custom_field_number' => '200',
            'cf_custom_field_numeric' => '1250',
            'cf_custom_field_radio' => $this->findCustomField('cf_custom_field_radio')->options->first()->getKey(),
            'cf_custom_field_select' => $this->findCustomField('cf_custom_field_select')->options->first()->getKey(),
            'cf_custom_field_text' => 'test-value',
            'cf_custom_field_textarea' => 'test-value',
            'cf_custom_field_timezone' => 'Europe/Berlin',
        ]);

        $this->assertDatabaseHas('model_has_custom_field_options', [
            'custom_field_id' => (string) $this->findCustomField('cf_custom_field_multiselect')->id,
            'option_id' => (string) $this->findCustomField('cf_custom_field_multiselect')->options->first()->getKey(),
        ]);

        $this->assertDatabaseHas('model_has_custom_field_options', [
            'custom_field_id' => (string) $this->findCustomField('cf_custom_field_checkbox')->id,
            'option_id' => (string) $this->findCustomField('cf_custom_field_checkbox')->options->first()->getKey(),
        ]);
    }

    public function assertThatResponseHasCustomFieldsValues($response): void
    {
        $response->assertJson(function (AssertableJson $json) {
            $json->where('cf_custom_field_timezone', 'Europe/Berlin')
                ->where('cf_custom_field_textarea', 'test-value')
                ->where('cf_custom_field_text', 'test-value')
                ->where('cf_custom_field_select', $this->findCustomField('cf_custom_field_select')->options->first()->getKey())
                ->where('cf_custom_field_radio', $this->findCustomField('cf_custom_field_radio')->options->first()->getKey())
                ->where('cf_custom_field_numeric', 1250)
                ->where('cf_custom_field_number', 200)
                ->has('cf_custom_field_multiselect', 1)
                ->where('cf_custom_field_multiselect.0.id', $this->findCustomField('cf_custom_field_multiselect')->options->first()->getKey())
                ->where('cf_custom_field_work_email', 'info@concordcrm.com')
                ->where('cf_custom_field_datetime', Carbon::parse('2021-12-05 10:00:00')->toJSON())
                ->where('cf_custom_field_date', Carbon::parse('2021-12-05')->toJSON())
                ->where('cf_custom_field_boolean', true)
                ->where('cf_custom_field_color_swatch', '#333333')
                ->where('cf_custom_field_url', 'https://concordcrm.com')
                ->has('cf_custom_field_multiselect', 1)
                ->where('cf_custom_field_checkbox.0.id', $this->findCustomField('cf_custom_field_checkbox')->options->first()->getKey())
                ->etc();
        });
    }

    protected function customFieldsPayload(): array
    {
        return $this->customFields()
            ->mapWithKeys(function ($field) {
                $value = 'test-value';

                if ($field->isMultiOptionable()) {
                    $value = [$field->customField->options->first()->getKey()];
                } elseif ($field->isOptionable()) {
                    $value = $field->customField->options->first()->getKey();
                } elseif ($field instanceof Date) {
                    $value = '2021-12-05';
                } elseif ($field instanceof DateTime) {
                    $value = '2021-12-05 10:00:00';
                } elseif ($field instanceof Timezone) {
                    $value = 'Europe/Berlin';
                } elseif ($field instanceof Numeric) {
                    $value = 1250.000;
                } elseif ($field instanceof Number) {
                    $value = 200;
                } elseif ($field instanceof Email) {
                    $value = 'info@concordcrm.com';
                } elseif ($field instanceof Boolean) {
                    $value = true;
                } elseif ($field instanceof Url) {
                    $value = 'https://concordcrm.com';
                } elseif ($field instanceof ColorSwatch) {
                    $value = '#333333';
                }

                return [$field->attribute => $value];
            })->all();
    }

    protected function performImportTest($overrides = []): void
    {
        $import = $this->performImportUpload($this->createFakeImportFile(
            [$this->createImportHeader(), $this->createImportRow($overrides)]
        ));

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertDatabaseCount($this->tableName(), 1);
    }

    protected function performImportWithCustomFieldsTest(): void
    {
        $this->signIn();
        $import = $this->performImportUpload($this->createFakeImportFile());

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertDatabaseCount($this->tableName(), 1);
        $this->assertThatDatabaseHasCustomFieldsValues();
    }

    protected function performImportWithDuplicateTest($overrides): void
    {
        $import = $this->performImportUpload($this->createFakeImportFile(
            [$this->createImportHeader(), $this->createImportRow($overrides)]
        ));

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertDatabaseCount($this->tableName(), 1);
    }

    protected function createFakeImportFile($rows = null): File
    {
        $rows = $rows ?: [$this->createImportHeader(), $this->createImportRow()];

        $tmpfile = tmpfile();

        foreach ($rows as $row) {
            fputcsv($tmpfile, $row);
        }

        return tap(new File('test.csv', $tmpfile), function ($file) use ($tmpfile) {
            $file->sizeToReport = fstat($tmpfile)['size'];
        });
    }

    protected function createImportHeader(): array
    {
        return $this->resource()->fieldsForImport()->map(function ($field) {
            return $field->label;
        })->all();
    }

    protected function createImportRow($overrides = []): array
    {
        return $this->resource()->fieldsForImport()->mapWithKeys(function ($field) {
            return [$field->attribute => $field->sampleValueForImport()];
        })->merge($this->customFieldsPayload())->mapWithKeys(function ($value, $attribute) use ($overrides) {
            if (array_key_exists($attribute, $overrides)) {
                $value = $overrides[$attribute];
            }

            return [$attribute => $value];
        })->values()->map(function ($value) {
            if (is_array($value)) {
                return implode(',', $value);
            }

            return $value;
        })->all();
    }

    protected function performExportTest(): void
    {
        $user = $this->signIn();

        $this->factory()->count(2)->create();

        try {
            $response = $this->postJson($this->endpoint().'/export', [
                'type' => 'csv',
                'period' => 'last_7_days',
            ])->assertOk()->assertDownload();

            $csvArray = $this->csvToArray($response->getFile()->getPathname());

            $export = $this->resource()->exportable($this->resource()->newQuery())->setUser($user);

            $fields = $export->fieldsWithLocalDateTime();

            $this->assertCount(2, $csvArray);
            $this->assertCount(count($csvArray[0]), $fields);

            $records = $this->resource()->exportQuery(
                app(ResourceRequest::class)->setResource($this->resourceName)
            )->get();

            $failMessage = 'Failed to assert that the field with attribute %s and "%s" value matches exported value "%s".';

            foreach ($records as $key => $record) {
                foreach ($fields as $field) {
                    $fieldValue = $field->resolveForExport($record);
                    $exportedValue = $csvArray[$key][$export->heading($field)];

                    if ($field instanceof Numeric) {
                        $fieldValue = (float) $fieldValue;
                        $exportedValue = (float) $exportedValue;
                    }

                    $this->assertEquals(
                        $fieldValue,
                        $exportedValue,
                        sprintf($failMessage, $field->attribute, $fieldValue, $exportedValue)
                    );
                }
            }
        } finally {
            if (is_file($response->getFile()->getPathname())) {
                unlink($response->getFile()->getPathname());
            }
        }
    }

    protected function performTestTableLoad(): void
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->tableEndpoint())
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total', 'pre_total',
                ],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);
    }

    protected function performTestTableCanLoadWithAllFields(): void
    {
        $this->signIn();

        $this->resource()->fieldsForIndex()->each(function ($field) {
            $field->tapIndexColumn(function (Column $column) {
                $column->hidden(false);
            });
        });

        $attributes = $this->resource()->fieldsForIndex()
            ->map(fn ($field) => $field->resolveIndexColumn())
            ->filter()
            ->map(fn ($column) => $column->attribute)
            ->all();

        $this->factory()->count(5)->create();

        $this->getJson($this->tableEndpoint())
            ->assertOk()
            ->assertJsonCount(5, 'data')->assertJsonStructure([
                'data' => [$attributes],
            ]);
    }

    protected function createRequestForTable($user = null): ResourceRequest
    {
        $user = $user ?: $this->createUser();
        $request = app(ResourceRequest::class);
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
