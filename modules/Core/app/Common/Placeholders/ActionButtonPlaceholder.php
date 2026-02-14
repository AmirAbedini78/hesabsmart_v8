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

namespace Modules\Core\Common\Placeholders;

class ActionButtonPlaceholder extends UrlPlaceholder
{
    /**
     * Indicates the starting interpolation
     */
    public string $interpolationStart = '{{{';

    /**
     * Indicates the ending interpolation
     */
    public string $interpolationEnd = '}}}';

    /**
     * Initialize new ActionButtonPlaceholder instance.
     *
     * @param  \Closure|mixed  $value
     */
    public function __construct($value = null, string $tag = 'action_button')
    {
        parent::__construct($value, $tag);

        $this->description(__('core::app.formatted_action_button'));
    }

    /**
     * Format the placeholder
     *
     * @return \Closure
     */
    public function format(?string $contentType = null)
    {
        // $text and $mustache are empty because of versions compatibility
        // previous this was {{{ action_button }}} only now it's {{#action_button}}Text{{/action_button}}
        return function ($text = '', $mustache = null) use ($contentType) {
            if ($contentType === 'text') {
                return parent::format();
            }

            return view('core::mail.action', [
                'url' => parent::format(),
                'text' => $text ?: __('core::mail_template.placeholders.view_record'),
            ])->render();
        };
    }
}
