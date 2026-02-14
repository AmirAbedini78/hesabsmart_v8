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
    'permissions' => 'Oprávnenia',
    'role' => 'Oprávnenie',
    'roles' => 'Oprávnenia',
    'name' => 'Názov',
    'create' => 'Vytvoriť oprávnenie',
    'edit' => 'Upraviť oprávnenia',
    'created' => 'Oprávnenie bolo úspešne vytvorené',
    'updated' => 'Oprávnenie bolo úspešne aktualizované',
    'deleted' => 'Oprávnenie bolo úspešne vymazané',
    'granted' => 'Ano',
    'revoked' => 'Nie',
    'capabilities' => [
        'access' => 'Prístup',
        'view' => 'Zobraziť',
        'delete' => 'Vymazať',
        'bulk_delete' => 'Hromadné vymazanie',
        'edit' => 'Upraviť',
        'all' => 'Všetky :resourceName',
        'owning_only' => 'Iba vlastné',
    ],
    'view_non_authorized_after_record_create' => 'Váš účet nemá oprávnenie na zobrazenie tohto záznamu, pretože nie ste vlastníkom záznamu. Po presmerovaní z tejto stránky nebudete mať prístup k záznamu.',
    'empty_state' => [
        'title' => 'Žiadne oprávnenia sa nenašli',
        'description' => 'Začnite vytvorením nového oprávnenia.',
    ],
];
