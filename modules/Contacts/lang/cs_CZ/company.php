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
    'company' => 'Firma',
    'companies' => 'Firmy',
    'add' => 'Přidat firmu',
    'dissociate' => 'Oddělit firmu',
    'child' => 'Sesterská firma | Sesterské firmy',
    'create' => 'Vytvořit firmu',
    'export' => 'Exportovat firmu',
    'total' => 'Firem spolu',
    'import' => 'Importovat firmy',
    'create_with' => 'Vytvořit firmu s :name',
    'associate_with' => 'Přiřadit firmu s :name',
    'associate_field_info' => 'Toto pole použijte k vyhledání a přiřazení existující firmy namísto vytvoření nové.',
    'no_contacts_associated' => 'Firma nemá přiřazeny žádné kontakty.',
    'no_deals_associated' => 'Firma nemá přiřazeny žádné obchody.',
    'exists_in_trash_by_email' => 'Firma s touto e-mailovou adresou již existuje v koši, nebudete moci vytvořit novou firmu se stejnou e-mailovou adresou. Chcete obnovit firmu, která byla přesunuta do koše?',
    'exists_in_trash_by_name' => 'Firma se stejným názvem již existuje v koši, chcete obnovit vymazanou firmu?',
    'exists_in_trash_by_phone' => 'Firma (:company) s následujícími čísly: :phone_numbers, již existuje v koši, chcete obnovit vymazanou firmu?',
    'possible_duplicate' => 'Možný duplicitní název firmy :display_name.',
    'count' => [
        'all' => '1 firma | :count firem',
    ],
    'notifications' => [
        'assigned' => 'Byli jste přiděleni do firmy :name uživatelem :user',
    ],
    'cards' => [
        'by_source' => 'Zdroje firem',
        'by_day' => 'Nové firmy',
    ],
    'settings' => [
        'automatically_associate_with_contacts' => 'Automaticky vytvářet a spojovat firmy s kontakty',
        'automatically_associate_with_contacts_info' => 'Automaticky přiřaďte kontakty k firmám na základě kontaktní e-mailové adresy a firemní domény.',
    ],
    'industry' => [
        'industries' => 'Odvětví',
        'industry' => 'Odvětví',
    ],
    'views' => [
        'all' => 'Všechny firmy',
        'my' => 'Moje firmy',
        'my_recently_assigned' => 'Moje nedávno přidělené firmy',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Uživatel, který přiřadil firmu',
    ],
    'workflows' => [
        'triggers' => [
            'created' => 'Vytvoření nové firmy',
        ],
        'actions' => [
            'fields' => [
                'email_to_company' => 'Firemní e-mail',
                'email_to_owner_email' => 'E-mail majitele',
                'email_to_creator_email' => 'E-mail asistenta',
                'email_to_contact' => 'Primární kontakt',
            ],
        ],
    ],
    'validation' => [
        'email' => [
            'unique' => 'Firma s tímto e-mailem již existuje.',
        ],
    ],
    'empty_state' => [
        'title' => 'Nevytvořili jste žádné firmy.',
        'description' => 'Začněte vytvořením nové firmy.',
    ],
];
