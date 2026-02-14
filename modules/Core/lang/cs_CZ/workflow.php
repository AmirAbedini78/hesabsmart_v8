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
    'create' => 'Vytvořit automatizaci',
    'workflows' => 'Automatizace',
    'title' => 'Název',
    'description' => 'Popis',
    'created' => 'Automatizace byla úspěšně vytvořena.',
    'updated' => 'Automatizace byla úspěšně aktualizována.',
    'deleted' => 'Automatizace byla úspěšně vymazána.',
    'when' => 'Kdy',
    'then' => 'Potom',
    'field_change_to' => 'Do',
    'total_executions' => 'Provedení: :total',
    'info' => 'Nástroj automatizace automatizuje vaše prodejní procesy. Mezi interní procesy, které lze automatizovat, patří vytváření aktivit, odesílání e-mailů, spouštění požadavků HTTP atp.',
    'validation' => [
        'invalid_webhook_url' => 'Webová adresa webhooku nesmí začínat řetězcem „https://“ nebo „http://',
    ],
    'actions' => [
        'webhook' => 'Spustit Webhook',
        'webhook_url_info' => 'Musí to být úplná, platná a veřejně dostupná URL adresa.',
    ],
    'fields' => [
        'with_header_name' => 'S názvem hlavičky (volitelné)',
        'with_header_value' => 'S hodnotou hlavičky (volitelné)',
        'for_owner' => 'Pro: Vlastník (odpovědná osoba)',
        'dates' => [
            'now' => 'Termín splnění: okamžitě',
            'in_1_day' => 'Termín splnění: jednou denně',
            'in_2_days' => 'Termín splnění: 2 krát za den',
            'in_3_days' => 'Termín splnění: 3 krát za den',
            'in_4_days' => 'Termín splnění: 4 krát za den',
            'in_5_days' => 'Termín splnění: 5 krát za den',
            'in_1_week' => 'Termín splnění: jednou týdně',
            'in_2_weeks' => 'Termín splnění: 2 krát za týden',
            'in_1_month' => 'Termín splnění: jednou měsíčně',
        ],
    ],
];
