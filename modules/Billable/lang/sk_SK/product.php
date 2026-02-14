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
    'create' => 'Vytvoriť produkt',
    'edit' => 'Upraviť produkt',
    'export' => 'Exportovať produkty',
    'import' => 'Importovať produkty',
    'created' => 'Produkt bol úspešne vytvorený',
    'updated' => 'Produkt bol úspešne aktualizovaný',
    'deleted' => 'Produkt bol úspešne odstránený',
    'related_products' => 'Súvisiace produkty',
    'manage' => 'Správa produktov',
    'name' => 'Názov',
    'description' => 'Popis',
    'table_heading' => 'Produkt',
    'tax' => 'Daň',
    'quantity' => 'Množstvo',
    'qty' => 'Množstvo',
    'unit_price' => 'Cena',
    'direct_cost' => 'Náklady',
    'unit' => 'Jednotka (ks, hod)',
    'sku' => 'SKU',
    'is_active' => 'Aktívny',
    'tax_rate' => 'Výška dane',
    'tax_label' => 'Názov dane',
    'tax_percent' => 'Percento dane',
    'discount' => 'Zľava',
    'amount' => 'Suma',
    'discount_percent' => 'Percentuálna zľava',
    'discount_amount' => 'Výška zľavy',
    'will_be_added_as_new' => ':name bude pridaný ako nový produkt',
    'total_products' => 'Celkový počet produktov',
    'total_sold' => 'Predané',
    'sold_amount_exc_tax' => 'Obrat (bez dane)',
    'interest_in_product' => 'Záujem',
    'resource_has_no_products' => 'Nie sú vytvorené žiadne produkty, začnite pridaním produktov',
    'exists_in_trash_by_name' => 'Produkt s rovnakým názvom už existuje v koši. Chcete obnoviť odstránený produkt?',
    'choose_or_enter' => 'Vyberte alebo zadajte produkt',
    'cards' => [
        'performance' => 'Predajnosť produktu',
        'performance_info' => 'Stĺpec "Záujem" zobrazuje všetky produkty, ktoré sú pridané k obchodom, zatiaľ čo stĺpec "Predané" zobrazuje produkty, ktoré sú pridané k obchodom a obchody sú označené ako vyhrané.',
    ],
    'views' => [
        'all' => 'Všetky produkty',
        'active' => 'Aktívne produkty',
    ],
    'count' => 'produktov: 0 | produktov: 1 | produktov: :count',
    'settings' => [
        'default_tax_type' => 'Zadávate ceny s daňou?',
        'default_discount_type' => 'Predvolený typ zľavy',
    ],
    'actions' => [
        'mark_as_active' => 'Označiť ako aktívny',
        'mark_as_inactive' => 'Označiť ako neaktívny',
        'update_unit_price' => 'Aktualizovať cenu',
        'update_tax_rate' => 'Aktualizovať výšku dane',
        'update_tax_label' => 'Aktualizovať názov dane',
    ],
    'validation' => [
        'sku' => [
            'unique' => 'Produkt s týmto SKU už existuje.',
        ],
    ],
    'empty_state' => [
        'title' => 'Nevytvorili ste žiadne produkty.',
        'description' => 'Ušetrite čas používaním preddefinovaných produktov.',
    ],
];
