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
    'brand' => 'Vlastná firma',
    'brands' => 'Vlastné firmy',
    'create' => 'Vytvoriť firmu',
    'update' => 'Aktualizovať firmu',
    'at_least_one_required' => 'Musí existovať aspoň jedna firma.',
    'form' => [
        'sections' => [
            'general' => 'Všeobecné',
            'navigation' => 'Navigácia',
            'email' => 'Email',
            'thank_you' => 'Poďakovanie',
            'signature' => 'Podpis',
            'pdf' => 'PDF',
        ],
        'is_default' => 'Toto je predvolená firma?',
        'name' => 'Oficiálny názov firmy',
        'display_name' => 'Ako chcete, aby sa firma zobrazovala vašim klientom?',
        'primary_color' => 'Vyberte základnú farbu firmy',
        'upload_logo' => 'Nahrajte logo firmy',
        'navigation' => [
            'background_color' => 'Farba pozadia navigácie',
            'upload_logo_info' => 'Ak máte tmavé pozadie, použite svetlé logo. Ak používate svetlú farbu pozadia, použite logo s tmavým textom.',
        ],
        'pdf' => [
            'default_font' => 'Predvolený font',
            'default_font_info' => 'Font :fontName poskytuje základnú podporu pre slušné pokrytie znakov Unicode. Uistite sa, že ste vybrali vhodné písmo, ak sa špeciálne alebo znaky Unicode nezobrazujú správne v dokumente PDF zmeňte font.',
            'size' => [
                'size' => 'Veľkosť',
                'a4' => 'A4',
                'letter' => 'List',
            ],
            'orientation' => [
                'orientation' => 'Orientácia',
                'portrait' => 'Na výšku',
                'landscape' => 'Na šírku',
            ],
        ],
        'email' => [
            'upload_logo_info' => 'Uistite sa, že je logo vhodné pre biele pozadie. Ak nenahráte žiadne logo, použije sa namiesto neho tmavé logo nahrané vo všeobecných nastaveniach.',
        ],
        'document' => [
            'send' => [
                'info' => 'Keď odošlete dokument',
                'subject' => 'Predvolený predmet',
                'message' => 'Predvolená e-mailová správa pri odosielaní dokumentu',
                'button_text' => 'Text tlačidla e-mailu',
            ],
            'sign' => [
                'info' => 'Keď niekto podpíše váš dokument',
                'subject' => 'Predvolený predmet e-mailu s poďakovaním',
                'message' => 'E-mailová správa na odoslanie, keď niekto podpíše váš dokument',
                'after_sign_message' => 'Čo by mala správa obsahovať po podpísaní?',
            ],
            'accept' => [
                'after_accept_message' => 'Čo by mala správa obsahovať po podpísaní (bez digitálneho podpisu)?',
            ],
        ],
        'signature' => [
            'bound_text' => 'Právne viazaný text',
        ],
    ],
    'delete_documents_usage_warning' => 'Firma je už spojená s dokumentmi, preto ju nemožno vymazať.',
    'created' => 'Firma bola úspešne vytvorená.',
    'updated' => 'Firma bola úspešne aktualizovaná.',
    'deleted' => 'Firma bola úspešne odstránená',
];
