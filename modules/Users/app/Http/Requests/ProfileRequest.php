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

namespace Modules\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Rules\StringRule;
use Modules\Core\Rules\UniqueRule;
use Modules\Core\Rules\ValidLocaleRule;
use Modules\Users\Models\User;

class ProfileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', StringRule::make()],
            'email' => [
                'required',
                StringRule::make(),
                'email',
                UniqueRule::make(User::class, $this->user()->id),
            ],
            'time_format' => ['required', 'string', Rule::in(config('core.time_formats'))],
            'date_format' => ['required', 'string', Rule::in(config('core.date_formats'))],
            'locale' => ['required', 'string', new ValidLocaleRule],
            'timezone' => ['required', 'string', 'timezone:all'],
            'default_landing_page' => ['nullable', StringRule::make()],
        ];
    }
}
