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

namespace Modules\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Modules\Core\Support\Makeable;

class StringRule implements ValidationRule, ValidatorAwareRule
{
    use Makeable;

    /**
     * The validator instance.
     *
     * @var \Illuminate\Validation\Validator
     */
    protected $validator;

    /**
     * The max length of the string.
     */
    public int|string $maxLength = 191;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('validation.string')->translate([
                'attribute' => $this->getAttribute($attribute),
            ]);
        } elseif (Str::length($value ?? '') > $this->maxLength) {
            $fail('validation.max.string')->translate([
                'attribute' => $this->getAttribute($attribute),
                'max' => $this->maxLength,
            ]);
        }
    }

    /**
     * Get the attribute for the validation rule translation.
     */
    protected function getAttribute(string $attribute): string
    {
        return $this->validator->customAttributes[$attribute] ?? $attribute;
    }

    /**
     * Set the string max length.
     */
    public function max(int|string $maxLength): static
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * Set the current validator.
     */
    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }
}
