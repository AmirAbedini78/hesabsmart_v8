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
    'brand' => 'Vlastní firma',
    'brands' => 'Vlastní firmy',
    'create' => 'Vytvořit firmu',
    'update' => 'Aktualizovat firmu',
    'at_least_one_required' => 'Musí existovat alespoň jedna firma.',
    'form' => [
        'sections' => [
            'general' => 'Obecné',
            'navigation' => 'Navigace',
            'email' => 'Email',
            'thank_you' => 'Poděkování',
            'signature' => 'Podpis',
            'pdf' => 'PDF',
        ],
        'is_default' => 'Toto je výchozí firma?',
        'name' => 'Oficiální název firmy',
        'display_name' => 'Jak chcete, aby se firma zobrazovala vašim klientům?',
        'primary_color' => 'Vyberte základní barvu firmy',
        'upload_logo' => 'Nahrajte logo firmy',
        'navigation' => [
            'background_color' => 'Barva pozadí navigace',
            'upload_logo_info' => 'Máte-li tmavé pozadí, použijte světlé logo. Pokud používáte světlou barvu pozadí, použijte logo s tmavým textem.',
        ],
        'pdf' => [
            'default_font' => 'Výchozí font',
            'default_font_info' => 'The :fontName font gives the most decent Unicode character coverage by default, make sure to select a proper font if special or unicode characters are not displayed properly on the PDF document.',
            'size' => [
                'size' => 'Velikost',
                'a4' => 'A4',
                'letter' => 'Letter',
            ],
            'orientation' => [
                'orientation' => 'Orientace',
                'portrait' => 'Na výšku',
                'landscape' => 'Na šířku',
            ],
        ],
        'email' => [
            'upload_logo_info' => 'Ujistěte se, že je logo vhodné pro bílé pozadí. Pokud nenahrajete žádné logo, použije se místo něj tmavé logo nahrané v obecných nastaveních.',
        ],
        'document' => [
            'send' => [
                'info' => 'Když odešlete dokument',
                'subject' => 'Výchozí předmět',
                'message' => 'Výchozí e-mailová zpráva při odesílání dokumentu',
                'button_text' => 'Text tlačítka e-mailu',
            ],
            'sign' => [
                'info' => 'Když někdo podepíše váš dokument',
                'subject' => 'Výchozí předmět e-mailu s poděkováním',
                'message' => 'E-mailová zpráva k odeslání, když někdo podepíše váš dokument',
                'after_sign_message' => 'Co by měla zpráva obsahovat po podepsání?',
            ],
            'accept' => [
                'after_accept_message' => 'Co by měla zpráva obsahovat po podepsání (bez digitálního podpisu)?',
            ],
        ],
        'signature' => [
            'bound_text' => 'Právně vázaný text',
        ],
    ],
    'delete_documents_usage_warning' => 'Firma je již spojena s dokumenty, proto ji nelze vymazat.',
    'created' => 'Firma byla úspěšně vytvořena.',
    'updated' => 'Firma byla úspěšně aktualizována.',
    'deleted' => 'Firma byla úspěšně odstraněna',
];
