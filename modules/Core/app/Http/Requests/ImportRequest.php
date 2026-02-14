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

namespace Modules\Core\Http\Requests;

class ImportRequest extends ResourceRequest
{
    use InteractsWithResourceFields;

    /**
     * The row number the request is intended for
     */
    public ?int $rowNumber = null;

    /**
     * Set the row number for the request.
     */
    public function setRowNumber(int $number): static
    {
        $this->rowNumber = $number;

        return $this;
    }

    /**
     * Get the row number for the request.
     */
    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the error messages for the current resource request.
     */
    public function messages(): array
    {
        return [
            //
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        //
    }
}
