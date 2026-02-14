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
    'permissions' => 'Permessi',
    'role' => 'Ruolo',
    'roles' => 'Ruoli',
    'name' => 'Nome',
    'create' => 'Crea Ruolo',
    'edit' => 'Modifica Ruolo',
    'created' => 'Ruolo creato con successo',
    'updated' => 'Ruolo aggiornato con successo',
    'deleted' => 'Ruolo eliminato con successo',
    'granted' => 'Concesso',
    'revoked' => 'Revocato',
    'capabilities' => [
        'access' => 'Accesso',
        'view' => 'Visualizza',
        'delete' => 'Elimina',
        'bulk_delete' => 'Elimina in blocco',
        'edit' => 'Modifica',
        'all' => 'Tutti :resourceName',
        'owning_only' => 'Solo di proprietà',
    ],
    'view_non_authorized_after_record_create' => 'Il tuo account non è autorizzato a visualizzare questo record poiché non sei il proprietario del record. Dopo il reindirizzamento da questa pagina, non potrai accedere al record.',
    'empty_state' => [
        'title' => 'Nessun ruolo',
        'description' => 'Inizia creando un nuovo ruolo.',
    ],
];
