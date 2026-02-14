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
    'products' => 'Prodotti',
    'product' => 'Prodotto',
    'create' => 'Crea Prodotto',
    'edit' => 'Modifica Prodotto',
    'export' => 'Esporta Prodotti',
    'import' => 'Importa Prodotti',
    'created' => 'Prodotto creato con successo',
    'updated' => 'Prodotto aggiornato con successo',
    'deleted' => 'Prodotto eliminato con successo',
    'related_products' => 'Prodotti Correlati',
    'manage' => 'Gestisci Prodotti',
    'name' => 'Nome',
    'description' => 'Descrizione',
    'table_heading' => 'Prodotto',
    'tax' => 'Imposta',
    'quantity' => 'Quantità',
    'qty' => 'QTY',
    'unit_price' => 'Prezzo Unitario',
    'direct_cost' => 'Costo Diretto',
    'unit' => 'Unità (kg, lotti)',
    'sku' => 'SKU',
    'is_active' => 'Attivo',
    'tax_rate' => 'Aliquota Fiscale',
    'tax_label' => 'Etichetta Tassa',
    'tax_percent' => 'Percentuale Tassa',
    'discount' => 'Sconto',
    'amount' => 'Importo',
    'discount_percent' => 'Percentuale Sconto',
    'discount_amount' => 'Importo Sconto',
    'will_be_added_as_new' => ':name sarà aggiunto come nuovo prodotto',
    'total_products' => 'Totale Prodotti',
    'total_sold' => 'Totale Venduto',
    'sold_amount_exc_tax' => 'Importo Venduto (esclusa imposta)',
    'interest_in_product' => 'Interesse per il Prodotto',
    'resource_has_no_products' => 'Nessun prodotto aggiunto, inizia selezionando un prodotto',
    'exists_in_trash_by_name' => 'Esiste già un prodotto con lo stesso nome nel cestino, vuoi ripristinare il prodotto eliminato?',
    'choose_or_enter' => 'Scegli o inserisci un prodotto',
    'cards' => [
        'performance' => 'Prestazioni del prodotto',
        'performance_info' => 'La colonna "Interesse per il prodotto" riflette tutti i prodotti aggiunti alle trattative, ma la colonna "Totale Venduto" riflette i prodotti aggiunti alle trattative segnate come vinte',
    ],
    'views' => [
        'all' => 'Tutti i Prodotti',
        'active' => 'Prodotti Attivi',
    ],
    'count' => '0 prodotti | 1 prodotto | :count prodotti',
    'settings' => [
        'default_tax_type' => 'Vendi i tuoi prodotti con aliquote inclusive di imposta?',
        'default_discount_type' => 'Tipo di sconto predefinito',
    ],
    'actions' => [
        'mark_as_active' => 'Segna come attivo',
        'mark_as_inactive' => 'Segna come inattivo',
        'update_unit_price' => 'Aggiorna prezzo',
        'update_tax_rate' => 'Aggiorna aliquota fiscale',
        'update_tax_label' => 'Aggiorna etichetta tassa',
    ],
    'validation' => [
        'sku' => [
            'unique' => 'Esiste già un prodotto con questo SKU.',
        ],
    ],
    'empty_state' => [
        'title' => 'Non hai creato alcun prodotto.',
        'description' => 'Risparmia tempo utilizzando prodotti predefiniti.',
    ],
];
