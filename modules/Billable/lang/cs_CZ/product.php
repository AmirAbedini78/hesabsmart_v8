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
    'products' => 'Produkty',
    'product' => 'Produkt',
    'create' => 'Vytvořit produkt',
    'edit' => 'Upravit produkt',
    'export' => 'Exportovat produkty',
    'import' => 'Importovat produkty',
    'created' => 'Produkt byl úspěšně vytvořen',
    'updated' => 'Produkt byl úspěšně aktualizován',
    'deleted' => 'Produkt bol úspešne odstránený',
    'related_products' => 'Související produkty',
    'manage' => 'Správa produktů',
    'name' => 'Název',
    'description' => 'Popis',
    'table_heading' => 'Produkt',
    'tax' => 'Daň',
    'quantity' => 'Množství',
    'qty' => 'Množství',
    'unit_price' => 'Cena',
    'direct_cost' => 'Náklady',
    'unit' => 'Jednotka (ks, hod)',
    'sku' => 'SKU',
    'is_active' => 'Aktivní',
    'tax_rate' => 'Výše daně',
    'tax_label' => 'Název daně',
    'tax_percent' => 'Procento daně',
    'discount' => 'Sleva',
    'amount' => 'Suma',
    'discount_percent' => 'Procentuální sleva',
    'discount_amount' => 'Výše slevy',
    'will_be_added_as_new' => ':name bude pridaný ako nový produkt',
    'total_products' => 'Celkový počet produktů',
    'total_sold' => 'Prodáno',
    'sold_amount_exc_tax' => 'Obrat (bez daně)',
    'interest_in_product' => 'Záujem',
    'resource_has_no_products' => 'Nejsou vytvořeny žádné produkty, začněte přidáním produktů',
    'exists_in_trash_by_name' => 'Produkt se stejným názvem již existuje v koši. Chcete obnovit odstraněný produkt?',
    'choose_or_enter' => 'Vyberte nebo zadejte produkt',
    'cards' => [
        'performance' => 'Prodejnost produktu',
        'performance_info' => 'Sloupec "Zájem" zobrazuje všechny produkty, které jsou přidány k obchodům, zatímco sloupec "Prodáno" zobrazuje produkty, které jsou přidány k obchodům a obchody jsou označeny jako vyhráno.',
    ],
    'views' => [
        'all' => 'Všechny produkty',
        'active' => 'Aktivní produkty',
    ],
    'count' => 'produktů: 0 | produktů: 1 | produktů: :count',
    'settings' => [
        'default_tax_type' => 'Zadáváte ceny s daní?',
        'default_discount_type' => 'Výchozí typ slevy',
    ],
    'actions' => [
        'mark_as_active' => 'Označit jako aktivní',
        'mark_as_inactive' => 'Označit jako neaktivní',
        'update_unit_price' => 'Aktualizovat cenu',
        'update_tax_rate' => 'Aktualizovat výši daně',
        'update_tax_label' => 'Aktualizovat název daně',
    ],
    'validation' => [
        'sku' => [
            'unique' => 'Produkt s tímto SKU již existuje.',
        ],
    ],
    'empty_state' => [
        'title' => 'Nevytvořili jste žádné produkty.',
        'description' => 'Ušetřete čas používáním předdefinovaných produktů.',
    ],
];
