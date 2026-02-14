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
    'create' => 'Crea Workflow',
    'workflows' => 'Workflow',
    'title' => 'Titolo',
    'description' => 'Descrizione',
    'created' => 'Workflow creato con successo.',
    'updated' => 'Workflow aggiornato con successo.',
    'deleted' => 'Workflow eliminato con successo',
    'when' => 'Quando',
    'then' => 'Allora',
    'field_change_to' => 'A',
    'total_executions' => 'Esecuzioni: :total',
    'info' => 'Lo strumento Workflow automatizza i tuoi processi di vendita. I processi interni che possono essere automatizzati includono la creazione di attività, l\'invio di e-mail, l\'attivazione di richieste HTTP, ecc.',
    'validation' => [
        'invalid_webhook_url' => 'L\'URL del webhook non deve iniziare con "https://" o "http://"',
    ],
    'actions' => [
        'webhook' => 'Attiva Webhook',
        'webhook_url_info' => 'Deve essere un URL completo, valido e pubblicamente accessibile.',
    ],
    'fields' => [
        'with_header_name' => 'Con nome intestazione (opzionale)',
        'with_header_value' => 'Con valore intestazione (opzionale)',
        'for_owner' => 'Per: Proprietario (Persona responsabile)',
        'dates' => [
            'now' => 'Con data di scadenza: al momento',
            'in_1_day' => 'Con data di scadenza: tra un giorno',
            'in_2_days' => 'Con data di scadenza: tra due giorni',
            'in_3_days' => 'Con data di scadenza: tra tre giorni',
            'in_4_days' => 'Con data di scadenza: tra quattro giorni',
            'in_5_days' => 'Con data di scadenza: tra cinque giorni',
            'in_1_week' => 'Con data di scadenza: tra 1 settimana',
            'in_2_weeks' => 'Con data di scadenza: tra 2 settimane',
            'in_1_month' => 'Con data di scadenza: tra 1 mese',
        ],
    ],
];
