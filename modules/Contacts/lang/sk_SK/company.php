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
    'add' => 'Pridať firmu',
    'dissociate' => 'Oddeliť firmu',
    'child' => 'Sesterská firma | Sesterské firmy',
    'create' => 'Vytvoriť firmu',
    'export' => 'Exportovať firmu',
    'total' => 'Firiem spolu',
    'import' => 'Importovať firmy',
    'create_with' => 'Vytvoriť firmu s :name',
    'associate_with' => 'Priradiť firmu s :name',
    'associate_field_info' => 'Toto pole použite na vyhľadanie a priradenie existujúcej firmy namiesto vytvorenia novej.',
    'no_contacts_associated' => 'Firma nemá priradené žiadne kontakty.',
    'no_deals_associated' => 'Firma nemá priradené žiadne obchody.',
    'exists_in_trash_by_email' => 'Firma s touto e-mailovou adresou už existuje v koši, nebudete môcť vytvoriť novú firmu s rovnakou e-mailovou adresou. Chcete obnoviť firmu, ktorá bola presunutá do koša?',
    'exists_in_trash_by_name' => 'Firma s rovnakým názvom už existuje v koši, chcete obnoviť vymazanú firmu?',
    'exists_in_trash_by_phone' => 'Firma (:company) s nasledujúcimi číslami: :phone_numbers, už existuje v koši, chcete obnoviť vymazanú firmu?',
    'possible_duplicate' => 'Možný duplicitný názov firmy :display_name.',
    'count' => [
        'all' => '1 firma | :count firiem',
    ],
    'notifications' => [
        'assigned' => 'Boli ste pridelení do firmy :name užívateľom :user',
    ],
    'cards' => [
        'by_source' => 'Zdroje firiem',
        'by_day' => 'Nové firmy',
    ],
    'settings' => [
        'automatically_associate_with_contacts' => 'Automaticky vytvárať a spájať firmy s kontaktmi',
        'automatically_associate_with_contacts_info' => 'Automaticky priraďte kontakty k firmám na základe kontaktnej e-mailovej adresy a firemnej domény.',
    ],
    'industry' => [
        'industries' => 'Odvetvia',
        'industry' => 'Odvetvie',
    ],
    'views' => [
        'all' => 'Všetky firmy',
        'my' => 'Moje firmy',
        'my_recently_assigned' => 'Moje nedávno pridelené firmy',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Používateľ, ktorý priradil firmu',
    ],
    'workflows' => [
        'triggers' => [
            'created' => 'Vytvorenie novej firmy',
        ],
        'actions' => [
            'fields' => [
                'email_to_company' => 'Firemný e-mail',
                'email_to_owner_email' => 'E-mail majiteľa',
                'email_to_creator_email' => 'E-mail asistenta',
                'email_to_contact' => 'Primárny kontakt',
            ],
        ],
    ],
    'validation' => [
        'email' => [
            'unique' => 'Firma s týmto e-mailom už existuje.',
        ],
    ],
    'empty_state' => [
        'title' => 'Nevytvorili ste žiadne firmy.',
        'description' => 'Začnite vytvorením novej firmy.',
    ],
];
