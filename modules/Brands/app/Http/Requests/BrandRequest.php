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

namespace Modules\Brands\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Modules\Brands\Models\Brand;
use Modules\Core\Rules\StringRule;
use Modules\Core\Rules\UniqueRule;

class BrandRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', StringRule::make(), UniqueRule::make(Brand::class, 'brand')],
            'display_name' => ['required', StringRule::make()],
            'is_default' => 'nullable|required|boolean',
            'config.primary_color' => 'hex_color',
            'config.pdf.font' => [
                Rule::requiredIfMethodPut($this),
                'string',
                Rule::in(Arr::pluck(config('contentbuilder.fonts'), 'font-family')),
            ],
            'config.pdf.orientation' => [Rule::requiredIfMethodPut($this), 'string', 'in:portrait,landscape'],
            'config.pdf.size' => [Rule::requiredIfMethodPut($this), 'string', 'in:a4,letter'],
            'config.signature.bound_text' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
            'config.document.mail_subject' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
            'config.document.mail_message' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
            'config.document.mail_button_text' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
            'config.document.signed_mail_subject' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
            'config.document.signed_mail_message' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
            'config.document.signed_thankyou_message' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
            'config.document.accepted_thankyou_message' => ['sometimes', Rule::requiredIfMethodPut($this), 'array'],
        ];
    }
}
