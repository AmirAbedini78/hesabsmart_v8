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

namespace Modules\Core\Workflow\Actions;

use Closure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\Url;
use Modules\Core\Workflow\Action;

class WebhookAction extends Action implements ShouldQueue
{
    /**
     * Action name
     */
    public static function name(): string
    {
        return __('core::workflow.actions.webhook');
    }

    /**
     * Run the trigger
     *
     * @return array
     */
    public function run()
    {
        $scheme = app()->isProduction() ? 'https' : 'http';

        Http::withoutVerifying()
            ->withHeaders($this->getHeaders())
            ->post($scheme.'://'.$this->url, $payload = $this->getPayload())
            ->onError(function ($error) {
                Log::debug('Webhook action failed with status: '.$error->status().', reason: '.$error->reason());
            });

        return $payload;
    }

    /**
     * Get the webhook request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return array_merge(with([], function ($headers) {
            if ($this->header_name) {
                $headers[$this->header_name] = $this->header_value;
            }

            return $headers;
        }), [
            'User-Agent' => 'Concord CRM',
        ]);
    }

    /**
     * Action available fields
     */
    public function fields(): array
    {
        return [
            Url::make('url')
                ->https()
                ->help(__('core::workflow.actions.webhook_url_info'))
                ->helpDisplay('text')
                ->rules(['required', function (string $attribute, mixed $value, Closure $fail) {
                    if (Str::startsWith($value, ['https://', 'http://'])) {
                        $fail('core::workflow.validation.invalid_webhook_url')->translate();
                    }
                }]),

            Text::make('header_name')->withMeta([
                'attributes' => [
                    'placeholder' => __('core::workflow.fields.with_header_name'),
                ],
            ]),

            Text::make('header_value')->withMeta([
                'attributes' => [
                    'placeholder' => __('core::workflow.fields.with_header_value'),
                ],
            ]),
        ];
    }

    /**
     * Set the trigger data
     *
     * @param  array  $data
     */
    public function setData($data): static
    {
        parent::setData($data);

        // We will set the payload while setting up the data for the action
        // this helps properly serialize the action when in the queue and keep
        // as the $data property is serialized for the action, after the action
        // is unserialized, we will still have the data from the previous request
        if ($this->viaModelTrigger()) {
            $query = method_exists($this->resource, 'webhookQuery') ?
                $this->resource->webhookQuery() :
                $this->resource->displayQuery();

            $payload = json_decode(json_encode(
                $this->resource->createJsonResource($query->find($this->model->getKey()), true)
            ), true);

            $this->setPayload($payload);
        }

        return $this;
    }

    /**
     * Get the request payload data
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set the request payload data
     *
     * @param  array  $data
     */
    public function setPayload($data)
    {
        $this->payload = (array) $data;

        return $this;
    }
}
