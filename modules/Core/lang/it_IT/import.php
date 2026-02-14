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
    'import' => 'Importa',
    'start' => 'Avvia Importazione',
    'import_records' => 'Importa Record',
    'import_in_progress' => 'Importazione in Corso',
    'download_sample' => 'Scarica Esempio',
    'history' => 'Cronologia Importazioni',
    'no_history' => 'Nessuna cronologia di importazione trovata.',
    'spreadsheet_columns' => 'Colonne del Foglio di Calcolo',
    'column_will_not_import' => 'non verrà importata',
    'records_being_imported_in_batches' => 'I record vengono importati in alcuni lotti, non navigare lontano da questa finestra fino a quando questo messaggio scompare.',
    'import_info' => 'Importa record da un file CSV, mappa automaticamente le colonne ai campi e riduci l\'inserimento manuale dei dati.',
    'date' => 'Data',
    'file_name' => 'Nome File',
    'user' => 'Utente',
    'total_imported' => 'Importati',
    'total_duplicates' => 'Duplicati',
    'total_skipped' => 'Saltati',
    'progress' => 'Progresso',
    'status' => 'Stato',
    'imported' => 'Record importati con successo',
    'revert' => 'Annulla',
    'revert_info' => 'Annullare un\'importazione eliminerà permanentemente tutti i record importati.',
    'why_skipped' => 'Perché?',
    'download_skip_file' => 'Scarica file dei record saltati',
    'skip_file' => 'File dei record saltati',
    'total_rows_skipped' => 'Totale righe saltate: :count',
    'skip_file_generation_info' => 'Un file dei record saltati viene generato ogni volta che la validazione di una riga del foglio di calcolo fallisce.',
    'skip_file_fix_and_continue' => 'Scarica il file dei record saltati per esaminare le righe fallite e il motivo per cui hanno fallito, dopo aver corretto le righe, carica il file corretto dei record saltati qui sotto per continuare il processo di importazione per l\'istanza di importazione corrente.',
    'upload_fixed_skip_file' => 'Carica file corretto dei record saltati',
    'steps' => [
        'step_1' => [
            'name' => 'Scarica Esempio',
            'description' => 'Guida alla formattazione del foglio di calcolo.',
        ],
        'step_2' => [
            'name' => 'Carica Foglio di Calcolo',
            'description' => 'Carica file per mappatura.',
        ],
        'step_3' => [
            'name' => 'Mappa Colonne',
            'description' => 'Mappa colonne con campi.',
        ],
        'step_4' => [
            'name' => 'Importa',
            'description' => 'Avvia il processo di importazione.',
        ],
    ],
    'from_file' => 'Importa Da File :file_type',
];
