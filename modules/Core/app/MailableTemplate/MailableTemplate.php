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

use Illuminate\Mail\Mailable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Modules\Core\Common\Placeholders\Placeholders as BasePlaceholders;
use Modules\Core\Models\MailableTemplate as MailableTemplateModel;
use Modules\Core\Resource\ResourcePlaceholders;

abstract class MailableTemplate extends Mailable
{
    /**
     * Provides the default mail template content to be is used when seeding the templates.
     */
    abstract public static function default(): DefaultMailable;

    /**
     * Get the mailable human readable name.
     */
    public static function name(): string
    {
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    /**
     * Build the view for the message.
     *
     * @return array
     */
    protected function buildView()
    {
        // Usually this may happen in tests where templates are not needed.
        if (! $model = $this->getModel()) {
            return ['text' => new HtmlString('Template does not exists')];
        }

        $renderer = $this->getTemplateRenderer($model);

        return array_filter([
            'html' => new HtmlString($renderer->renderHtmlLayout()),
            'text' => new HtmlString($renderer->renderTextLayout()),
        ]);
    }

    /**
     * Build the view data for the message.
     *
     * @return array
     */
    public function buildViewData()
    {
        return $this->placeholders()?->parse() ?: parent::buildViewData();
    }

    /**
     * Build the subject for the message.
     *
     * @param  \Illuminate\Mail\Message|\Modules\MailClient\Client\Client  $buildable
     * @return static
     */
    protected function buildSubject($buildable)
    {
        if ($model = $this->getModel()) {
            $buildable->subject($this->getTemplateRenderer($model)->renderSubject());
        } else {
            $buildable->subject('Template does not exists');
        }

        return $this;
    }

    /**
     * Get the mailable template subject.
     *
     * @return string|null
     */
    protected function getTemplateSubject()
    {
        return $this->subject ?? $this->getModel()->getSubject() ?? $this->name();
    }

    /**
     * Get the mailable template model.
     */
    public function getModel(): ?MailableTemplateModel
    {
        return once(function () {
            $locale = $this->locale ?? config('app.fallback_locale');

            $model = MailableTemplateModel::forLocale($locale, static::class)->first();

            if (! $model) {
                $model = MailableTemplateModel::forLocale(
                    config('app.fallback_locale'),
                    static::class
                )->first();
            }

            return $model;
        });
    }

    /**
     * Creates alternative text message from the given HTML.
     *
     * @param  string  $html
     * @return string
     */
    protected static function altMessageFromHtml($html)
    {
        return html_to_text($html);
    }

    /**
     * Get the mailable template content rendered.
     */
    protected function getTemplateRenderer(MailableTemplateModel $template): Renderer
    {
        return app(Renderer::class, [
            'htmlTemplate' => $template->getHtmlTemplate(),
            'subject' => $this->getTemplateSubject(),
            'placeholders' => $this->placeholders(),
            'htmlLayout' => $this->getHtmlLayout(),
            'textTemplate' => $template->getTextTemplate() ?: static::altMessageFromHtml($template->getHtmlTemplate()),
            'textLayout' => $this->getTextLayout(),
        ]);
    }

    /**
     * Get the mailable HTML layout.
     */
    public function getHtmlLayout(): ?string
    {
        $default = config('core.mailables.layout');

        if (file_exists($default)) {
            return file_get_contents($default) ?: null;
        }

        return null;
    }

    /**
     * Get the mailable text layout.
     */
    public function getTextLayout(): ?string
    {
        return null;
    }

    /**
     * Provide the defined mailable template placeholders.
     */
    public function placeholders(): ResourcePlaceholders|BasePlaceholders|null
    {
        return null;
    }

    /**
     * The Mailable build method.
     *
     * @see  buildSubject, buildView, send
     *
     * @return static
     */
    public function build()
    {
        return $this;
    }

    /**
     * Seed the mailable in database as mail template.
     */
    public static function seed(string $locale = 'en'): MailableTemplateModel
    {
        $mailable = get_called_class();

        return MailableTemplateModel::where('locale', $locale)
            ->where('mailable', $mailable)
            ->firstOr(
                function () use ($locale, $mailable) {
                    $default = static::default();

                    return tap((new MailableTemplateModel)->forceFill([
                        'locale' => $locale,
                        'subject' => $default->subject(),
                        'html_template' => $default->htmlMessage(),
                        'text_template' => $default->textMessage(),
                        'mailable' => $mailable,
                        'name' => static::name(),
                    ]))->save();
                });
    }
}
