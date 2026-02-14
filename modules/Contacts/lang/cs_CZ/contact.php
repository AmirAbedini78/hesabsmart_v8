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
    'convert' => 'Převést na kontakt',
    'create' => 'Vytvořit kontakt',
    'add' => 'Přidat kontakt',
    'total' => 'Celkový počet kontaktů',
    'import' => 'Importovat kontakty',
    'export' => 'Exportovať kontakty',
    'no_companies_associated' => 'Ke kontaktu nebyla přiřazena žádná firma.',
    'no_deals_associated' => 'Ke kontaktu nebyl přiřazen žádný obchod.',
    'works_at' => ':job_title v :company',
    'create_with' => 'Vytvořit kontakt s :name',
    'associate_with' => 'Přiřadit kontakt s :name',
    'associated_company' => 'Přiřazený kontakt firmy',
    'dissociate' => 'Oddělit kontakt',
    'exists_in_trash_by_email' => 'Kontakt s touto e-mailovou adresou již existuje v koši, nebudete moci vytvořit nový kontakt se stejnou e-mailovou adresou. Chcete obnovit kontakt?',
    'exists_in_trash_by_phone' => 'Kontakt (:contact) s následujícími čísly: :phone_numbers, již existuje v koši, chcete obnovit kontakt ?',
    'possible_duplicate' => 'Možný duplicitní kontakt :display_name.',
    'associate_field_info' => 'Toto pole použijte k vyhledání a přiřazení existujícího kontaktu namísto vytváření nového kontaktu.',
    'cards' => [
        'recently_created' => 'Nedávno vytvořené kontakty',
        'recently_created_info' => 'Zobrazuje se posledních :total vytvořených kontaktů za posledních :days dní, seřazené podle nejnovějších kontaktů.',
        'by_day' => 'Nové kontakty',
        'by_source' => 'Zdroje kontaktů',
    ],
    'count' => [
        'all' => '1 kontakt | :count kontaktů',
    ],
    'notifications' => [
        'assigned' => 'Byli jste přiřazeni ke kontaktu :name uživatelům :user',
    ],
    'views' => [
        'all' => 'Všechny kontakty',
        'my' => 'Mé kontakty',
        'my_recently_assigned' => 'Moje nedávno přidělené kontakty',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Uživatel, který přiřadil kontakt',
    ],
    'workflows' => [
        'triggers' => [
            'created' => 'Vytvořený nový kontakt',
        ],
        'actions' => [
            'fields' => [
                'email_to_contact' => 'Kontaktní e-mail',
                'email_to_owner_email' => 'E-mail vlastníka',
                'email_to_creator_email' => 'E-mail tvůrce',
                'email_to_company' => 'Kontaktujte primární společnost',
            ],
        ],
    ],
    'validation' => [
        'email' => [
            'unique' => 'Kontakt nebo člen skupiny s tímto e-mailem již existuje.',
        ],
        'phone' => [
            'unique' => 'Kontakt s tímto telefonním číslem již existuje.',
        ],
    ],
    'empty_state' => [
        'title' => 'Nevytvořili jste žádné kontakty.',
        'description' => 'Začněte nyní organizovat své kontakty.',
    ],
];
