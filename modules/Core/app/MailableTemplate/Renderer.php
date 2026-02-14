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

namespace Modules\Core\MailableTemplate;

use Illuminate\Support\Str;
use Modules\Core\Common\Placeholders\Placeholders;
use Modules\Core\MailableTemplate\Exceptions\CannotRenderMailableTemplate;
use Mustache_Engine;

class Renderer
{
    /**
     * Initialize new Renderer instance.
     */
    public function __construct(
        protected string $htmlTemplate,
        protected string $subject,
        protected Mustache_Engine $mustache,
        protected ?Placeholders $placeholders = null,
        protected ?string $htmlLayout = null,
        protected ?string $textTemplate = null,
        protected ?string $textLayout = null,
    ) {}

    /**
     * Render mail template HTML layout
     *
     * @return string|null
     */
    public function renderHtmlLayout()
    {
        $body = $this->mustache->render(
            $this->htmlTemplate,
            $this->placeholders?->parse(),
        );

        return $this->renderInLayout($body, $this->htmlLayout);
    }

    /**
     * Render mail template text layout
     *
     * @return string|null
     */
    public function renderTextLayout()
    {
        if (! $this->textTemplate) {
            return null;
        }

        $body = $this->mustache->render(
            $this->textTemplate,
            $this->placeholders?->parse('text')
        );

        return $this->renderInLayout($body, $this->textLayout);
    }

    /**
     * Render mail template subject
     *
     * @return string
     */
    public function renderSubject()
    {
        return $this->mustache->render(
            $this->subject,
            $this->placeholders?->parse('text')
        );
    }

    /**
     * Render mail template content in layout
     *
     * @return string
     *
     * @throws \Modules\Core\MailableTemplate\Exceptions\CannotRenderMailableTemplate
     */
    protected function renderInLayout(string $body, ?string $layout)
    {
        $this->guardAgainstInvalidLayout($layout ??= '{{{ mailBody }}}');

        $data = array_merge(['mailBody' => $body], $this->placeholders?->parse());

        return $this->mustache->render($layout, $data);
    }

    /**
     * Guard layout body
     *
     * @return void
     *
     * @throws \Modules\Core\MailableTemplate\Exceptions\CannotRenderMailableTemplate
     *
     * Ensures that body placeholder exists in the layout
     */
    protected function guardAgainstInvalidLayout(string $layout)
    {
        $bodyTag = [
            '{{{mailBody}}}',
            '{{{ mailBody }}}',
            '{{mailBody}}',
            '{{ mailBody }}',
            '{{ $mailBody }}',
            '{!! $mailBody !!}',
        ];

        if (! Str::contains($layout, $bodyTag)) {
            throw CannotRenderMailableTemplate::layoutDoesNotContainABodyPlaceHolder();
        }
    }
}
