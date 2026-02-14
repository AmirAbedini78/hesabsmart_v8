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

namespace Modules\Contacts\Cards;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Modules\Contacts\Criteria\ViewAuthorizedContactsCriteria;
use Modules\Contacts\Models\Contact;
use Modules\Core\Card\TableCard;

class RecentlyCreatedContacts extends TableCard
{
    /**
     * Limit the number of records shown in the table
     *
     * @var int
     */
    protected $limit = 20;

    /**
     * Created in the last 30 days
     *
     * @var int
     */
    protected $days = 30;

    /**
     * Provide the table items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function items(Request $request): iterable
    {
        return Contact::select(['id', 'first_name', 'last_name', 'created_at', 'email'])
            ->criteria(ViewAuthorizedContactsCriteria::class)
            ->where('created_at', '>', Carbon::asCurrentTimezone()->subDays($this->days)->inAppTimezone())
            ->latest()
            ->limit($this->limit)
            ->get()
            ->map(fn (Contact $contact) => [
                'id' => $contact->id,
                'display_name' => $contact->full_name,
                'email' => $contact->email,
                'created_at' => $contact->created_at,
                'path' => $contact->resource()->viewRouteFor($contact),
            ]);
    }

    /**
     * Provide the table fields
     */
    public function fields(): array
    {
        return [
            ['key' => 'display_name', 'label' => __('contacts::contact.contact')],
            ['key' => 'email', 'label' => __('contacts::fields.contacts.email')],
            ['key' => 'created_at', 'label' => __('core::app.created_at')],
        ];
    }

    /**
     * Card title
     */
    public function name(): string
    {
        return __('contacts::contact.cards.recently_created');
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'helpText' => __('contacts::contact.cards.recently_created_info', ['total' => $this->limit, 'days' => $this->days]),
        ]);
    }
}
