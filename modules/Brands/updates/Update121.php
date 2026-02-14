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

use Illuminate\Support\Arr;
use Modules\Brands\Models\Brand;
use Modules\Core\Facades\Innoclapps;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        if (is_null(settings('_brands_localization_migrated'))) {
            $localizeableConfigKeys = [
                'document.mail_subject',
                'document.mail_message',
                'document.mail_button_text',
                'document.signed_mail_subject',
                'document.signed_mail_message',
                'document.signed_thankyou_message',
                'document.accepted_thankyou_message',
                'signature.bound_text',
            ];

            Brand::get()->each(function ($brand) use ($localizeableConfigKeys) {
                foreach ($localizeableConfigKeys as $key) {
                    $config = $brand->config;
                    $value = Arr::get($config, $key);

                    if (! is_array($value)) {
                        $newValue = [];

                        foreach (Innoclapps::locales() as $locale) {
                            $newValue[$locale] = $value;
                        }

                        Arr::set($config, $key, $newValue);
                        $brand->config = $config;
                    }
                }
                $brand->saveQuietly();
            });

            settings(['_brands_localization_migrated' => true]);
        }
    }

    public function shouldRun(): bool
    {
        return is_null(settings('_brands_localization_migrated'));
    }
};
