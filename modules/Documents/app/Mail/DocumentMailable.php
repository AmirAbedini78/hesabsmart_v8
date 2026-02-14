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

namespace Modules\Documents\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Documents\Models\Document;
use Modules\MailClient\Mail\SendsMailableViaEmailAccount;

abstract class DocumentMailable extends Mailable
{
    use Queueable, SendsMailableViaEmailAccount, SerializesModels;

    public ?string $logo;

    public string $primaryColor;

    /**
     * Create a DocumentMailable instance.
     */
    public function __construct(public Document $document)
    {
        $this->logo = $document->brand->logoMailUrl ?: url(config('core.logo.dark'));
        $this->primaryColor = $document->brand->config['primary_color'];
    }

    /**
     * Provide the email account id.
     */
    protected function emailAccountId(): ?int
    {
        // What about other email than SendDocument?
        // Which account should be used system or the one selected in document send section?
        if ($account = $this->document->data['send_mail_account_id']) {
            return (int) $account;
        }

        return null;
    }
}
