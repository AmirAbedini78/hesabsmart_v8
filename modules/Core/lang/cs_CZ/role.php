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
    'permissions' => 'Oprávnění',
    'role' => 'Oprávnění',
    'roles' => 'Oprávnění',
    'name' => 'Název',
    'create' => 'Vytvořit oprávnění',
    'edit' => 'Upravit oprávnění',
    'created' => 'Oprávnění bylo úspěšně vytvořeno',
    'updated' => 'Oprávnění bylo úspěšně aktualizováno',
    'deleted' => 'Oprávnění bylo úspěšně vymazáno',
    'granted' => 'Ano',
    'revoked' => 'Ne',
    'capabilities' => [
        'access' => 'Přístup',
        'view' => 'Zobrazit',
        'delete' => 'Vymazat',
        'bulk_delete' => 'Hromadné vymazání',
        'edit' => 'Upravit',
        'all' => 'Všechny :resourceName',
        'owning_only' => 'Pouze vlastní',
    ],
    'view_non_authorized_after_record_create' => 'Váš účet nemá oprávnění k zobrazení tohoto záznamu, protože nejste vlastníkem záznamu. Po přesměrování z této stránky nebudete mít přístup k záznamu.',
    'empty_state' => [
        'title' => 'Žádná oprávnění nebyla nalezena',
        'description' => 'Začněte vytvořením nového oprávnění.',
    ],
];
