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

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Modules\Core\Settings\DefaultSettings;

class SettingRequest extends FormRequest
{
    /**
     * The original settings.
     */
    protected ?array $originalValues = null;

    /**
     * Save the settings via request.
     */
    public function saveSettings(): void
    {
        $this->collect()
            ->filter($this->filterSettingsForStorage(...))
            ->each(function ($value, $name) {
                is_null($value) ? settings()->forget($name) : settings()->set($name, $value);
            })
            ->whenNotEmpty(settings()->save(...));
    }

    /**
     * Get the original settings values.
     */
    public function getOriginalValues(?string $name = null): array|string|null
    {
        $original = $this->originalValues ?? settings()->all();

        return $name ? ($original[$name] ?? null) : $original;
    }

    /**
     * Filter the settings that are allowed for storage.
     */
    protected function filterSettingsForStorage(mixed $value, string $name): bool
    {
        $required = DefaultSettings::getRequired();

        if (in_array($name, $required) && empty($value)) {
            return false;
        }

        if (Str::startsWith($name, '_')) {
            return false;
        }

        // Settings filter for storage flag

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [];
    }
}
