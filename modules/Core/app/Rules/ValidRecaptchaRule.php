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
use Illuminate\Support\Facades\Http;
use Modules\Core\Facades\ReCaptcha;

class ValidRecaptchaRule implements ValidationRule
{
    /**
     * The endpoint to verify recaptcha
     */
    protected string $verifyEndpoint = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Http::asForm()->post($this->verifyEndpoint, [
            'secret' => ReCaptcha::getSecretKey(),
            'response' => $value,
        ])['success']) {
            $fail('validation.recaptcha')->translate();
        }
    }
}
