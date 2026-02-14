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
    'import' => 'Importovat',
    'start' => 'Začít import',
    'import_records' => 'Importované záznamy',
    'import_in_progress' => 'Probíhá import',
    'download_sample' => 'Stáhnout ukázku',
    'history' => 'Historie importu',
    'no_history' => 'Nebyla nalezena žádná historie importu.',
    'spreadsheet_columns' => 'Sloupce tabulky',
    'column_will_not_import' => 'nebude importováno',
    'records_being_imported_in_batches' => 'Záznamy se importují v několika dávkách, nezavírejte toto okno, dokud tato zpráva nezmizí.',
    'import_info' => 'Importujte záznamy ze souboru CSV, automaticky mapujte sloupce na pole a omezte manuální zadávání údajů.',
    'date' => 'Datum',
    'file_name' => 'Název souboru',
    'user' => 'Uživatel',
    'total_imported' => 'Importováno',
    'total_duplicates' => 'Duplikáty',
    'total_skipped' => 'Přeskočeno',
    'progress' => 'Progres',
    'status' => 'Stav',
    'imported' => 'Záznamy byly úspěšně importovány',
    'revert' => 'Vrátit zpět',
    'revert_info' => 'Vrácením importu se trvale odstraní všechny importované záznamy.',
    'why_skipped' => 'Proč?',
    'download_skip_file' => 'Stáhnout soubor přeskočených položek',
    'skip_file' => 'Neúplný import',
    'total_rows_skipped' => 'Vynechaných řádků: :count',
    'skip_file_generation_info' => 'Při každém selhání validace řádku tabulky se vytvoří soubor přeskočených položek.',
    'skip_file_fix_and_continue' => 'Stáhněte si soubor s přeskočenými řádky pro kontrolu neúspěšných řádků a zjištění důvodů selhání. Po úpravě řádků nahrajte opravený soubor přeskočení níže, abyste mohli pokračovat v procesu importu pro danou instanci.',
    'upload_fixed_skip_file' => 'Nahrát opravený soubor',
    'steps' => [
        'step_1' => [
            'name' => 'Stáhnout ukázku',
            'description' => 'Příručka formátování tabulek.',
        ],
        'step_2' => [
            'name' => 'Nahrát tabulku',
            'description' => 'Nahrát soubor pro mapování.',
        ],
        'step_3' => [
            'name' => 'Přiřadit sloupce',
            'description' => 'Přiřadit sloupce k polím.',
        ],
        'step_4' => [
            'name' => 'Importovat',
            'description' => 'Spustit import.',
        ],
    ],
    'from_file' => 'Importovat ze souboru typu :file_type',
];
