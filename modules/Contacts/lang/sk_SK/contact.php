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

return [
    'contact' => 'Kontakt',
    'contacts' => 'Kontakty',
    'convert' => 'Konvertovať na kontakt',
    'create' => 'Vytvoriť kontakt',
    'add' => 'Pridať kontakt',
    'total' => 'Celkový počet kontaktov',
    'import' => 'Importovať kontakty',
    'export' => 'Exportovať kontakty',
    'no_companies_associated' => 'Ku kontaktu nebola priradená žiadna firma.',
    'no_deals_associated' => 'Ku kontaktu nebol priradený žiadny obchod.',
    'works_at' => ':job_title v :company',
    'create_with' => 'Vytvoriť kontakt s :name',
    'associate_with' => 'Priradiť kontakt s :name',
    'associated_company' => 'Priradený kontakt firmy',
    'dissociate' => 'Oddeliť kontakt',
    'exists_in_trash_by_email' => 'Kontakt s touto e-mailovou adresou už existuje v koši, nebudete môcť vytvoriť nový kontakt s rovnakou e-mailovou adresou. Chcete obnoviť kontakt?',
    'exists_in_trash_by_phone' => 'Kontakt (:contact) s nasledujúcimi číslami: :phone_numbers, už existuje v koši, chcete obnoviť kontakt ?',
    'possible_duplicate' => 'Možný duplicitný kontakt :display_name.',
    'associate_field_info' => 'Toto pole použite na vyhľadanie a priradenie existujúceho kontaktu namiesto vytvárania nového kontaktu.',
    'cards' => [
        'recently_created' => 'Nedávno vytvorené kontakty',
        'recently_created_info' => 'Zobrazuje sa posledných :total vytvorených kontaktov za posledných :days dní, zoradené podľa najnovších kontaktov.',
        'by_day' => 'Nové kontakty',
        'by_source' => 'Zdroje kontaktov',
    ],
    'count' => [
        'all' => '1 kontakt | :count kontaktov',
    ],
    'notifications' => [
        'assigned' => 'Boli ste priradení ku kontaktu :name používateľom :user',
    ],
    'views' => [
        'all' => 'Všetky kontakty',
        'my' => 'Moje kontakty',
        'my_recently_assigned' => 'Moje nedávno pridelené kontakty',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Používateľ, ktorý priradil kontakt',
    ],
    'workflows' => [
        'triggers' => [
            'created' => 'Vytvorený nový kontakt',
        ],
        'actions' => [
            'fields' => [
                'email_to_contact' => 'Kontaktný e-mail',
                'email_to_owner_email' => 'E-mail vlastníka',
                'email_to_creator_email' => 'E-mail tvorcu',
                'email_to_company' => 'Kontaktujte primárnu spoločnosť',
            ],
        ],
    ],
    'validation' => [
        'email' => [
            'unique' => 'Kontakt alebo člen skupiny s týmto e-mailom už existuje.',
        ],
        'phone' => [
            'unique' => 'Kontakt s týmto telefónnym číslom už existuje.',
        ],
    ],
    'empty_state' => [
        'title' => 'Nevytvorili ste žiadne kontakty.',
        'description' => 'Začnite teraz organizovať svoje kontakty.',
    ],
];
