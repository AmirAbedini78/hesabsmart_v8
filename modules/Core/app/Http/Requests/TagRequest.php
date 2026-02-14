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

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Modules\Core\Rules\StringRule;

class TagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => array_filter([
                'required', StringRule::make(), function (string $attribute, mixed $value, Closure $fail) {
                    if (str_contains($value, ',')) {
                        $fail(__('core::tags.validation.coma'))->translate();
                    }
                },
                $this->getUniqueTagRule(),
            ]),
            'swatch_color' => ['required', 'hex_color'],
        ];
    }

    /**
     * Get the unique tag validation rule.
     */
    protected function getUniqueTagRule(): ?Unique
    {
        if ($this->isMethod('POST')) {
            return null;
        }

        $tag = $this->route('tag');
        $uniqueRule = Rule::unique('tags', 'name')->ignore($tag);

        if ($tag->type) {
            return $uniqueRule->where('type', $tag->type);
        }

        return $uniqueRule->using(fn ($query) => $query->whereNull('type'));
    }
}
