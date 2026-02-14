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

use Modules\Core\Models\MailableTemplate;
use Modules\Updater\UpdatePatcher;

return new class extends UpdatePatcher
{
    public function run(): void
    {
        $templates = MailableTemplate::get();

        foreach ($templates as $template) {
            foreach ($template->getPlaceholders()->all() as $placeholder) {
                if (str_contains($placeholder->tag, '.')) {
                    $p = explode('.', $placeholder->tag);

                    $prefix = $p[0];
                    $tag = $p[1];

                    $search = ["{{ $tag", '{{'.$tag];
                    $replace = "{{ $prefix.$tag";

                    $template->html_template = str_replace($search, $replace, $template->html_template);
                    $template->subject = str_replace($search, $replace, $template->subject);
                }
            }

            // Fix non-existent assigned placeholder that was added via seeded default template content.
            $template->html_template = str_replace(['{{ assigned', '{{assigned'], '{{ activity.user', $template->html_template);

            $template->save();
        }

        settings(['_migrated_to_new_templates_placeholders' => true]);
    }

    public function shouldRun(): bool
    {
        return settings('_migrated_to_new_templates_placeholders') !== true;
    }
};
