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
    'create' => 'Vytvoriť automatizáciu',
    'workflows' => 'Automatizácie',
    'title' => 'Názov',
    'description' => 'Popis',
    'created' => 'Automatizácia bola úspešne vytvorená.',
    'updated' => 'Automatizácia bola úspešne aktualizovaná.',
    'deleted' => 'Automatizácia bola úspešne vymazaná.',
    'when' => 'Kedy',
    'then' => 'Potom',
    'field_change_to' => 'Do',
    'total_executions' => 'Vykonania: :total',
    'info' => 'Nástroj automatizácia automatizuje vaše predajné procesy. Medzi interné procesy, ktoré je možné automatizovať, patrí vytváranie aktivít, odosielanie e-mailov, spúšťanie požiadaviek HTTP atď.',
    'validation' => [
        'invalid_webhook_url' => 'Webová adresa webhooku nesmie začínať reťazcom „https://“ alebo „http://',
    ],
    'actions' => [
        'webhook' => 'Spustiť Webhook',
        'webhook_url_info' => 'Musí to byť úplná, platná a verejne dostupná URL adresa.',
    ],
    'fields' => [
        'with_header_name' => 'S názvom hlavičky (voliteľné)',
        'with_header_value' => 'S hodnotou hlavičky (voliteľné)',
        'for_owner' => 'Pre: Vlastník (zodpovedná osoba)',
        'dates' => [
            'now' => 'Termín splnenia: okamžite',
            'in_1_day' => 'Termín splnenia: raz denne',
            'in_2_days' => 'Termín splnenia: 2 krát za deň',
            'in_3_days' => 'Termín splnenia: 3 krát za deň',
            'in_4_days' => 'Termín splnenia: 4 krát za deň',
            'in_5_days' => 'Termín splnenia: 5 krát za deň',
            'in_1_week' => 'Termín splnenia: raz týždenne',
            'in_2_weeks' => 'Termín splnenia: 2 krát za týždeň',
            'in_1_month' => 'Termín splnenia: raz mesačne',
        ],
    ],
];
