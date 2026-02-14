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
    'company' => 'Azienda',
    'companies' => 'Aziende',
    'add' => 'Aggiungi Azienda',
    'dissociate' => 'Dissocia Azienda',
    'child' => 'Filiale | Filiali',
    'create' => 'Crea Azienda',
    'export' => 'Esporta Aziende',
    'total' => 'Totale Aziende',
    'import' => 'Importa Aziende',
    'create_with' => 'Crea Azienda con :name',
    'associate_with' => 'Associa Azienda con :name',
    'associate_field_info' => 'Usa questo campo per trovare e associare un\'azienda esistente invece di crearne una nuova.',
    'no_contacts_associated' => 'L\'azienda non ha contatti associati.',
    'no_deals_associated' => 'L\'azienda non ha trattative associate.',
    'exists_in_trash_by_email' => 'Esiste già un\'azienda con questo indirizzo e-mail nel cestino, non sarà possibile creare una nuova azienda con lo stesso indirizzo e-mail. Vuoi ripristinare l\'azienda eliminata?',
    'exists_in_trash_by_name' => 'Esiste già un\'azienda con lo stesso nome nel cestino. Vuoi ripristinare l\'azienda eliminata?',
    'exists_in_trash_by_phone' => 'L\'azienda (:company) con i seguenti numeri: :phone_numbers, esiste già nel cestino. Vuoi ripristinare l\'azienda eliminata?',
    'possible_duplicate' => 'Possibile duplicato dell\'azienda :display_name.',
    'count' => [
        'all' => '1 azienda | :count aziende',
    ],
    'notifications' => [
        'assigned' => 'Sei stato assegnato a un\'azienda :name da :user',
    ],
    'cards' => [
        'by_source' => 'Aziende per fonte',
        'by_day' => 'Aziende per giorno',
    ],
    'settings' => [
        'automatically_associate_with_contacts' => 'Crea e associa automaticamente aziende con contatti',
        'automatically_associate_with_contacts_info' => 'Associa automaticamente i contatti con le aziende in base all\'indirizzo e-mail del contatto e al dominio dell\'azienda.',
    ],
    'industry' => [
        'industries' => 'Settori',
        'industry' => 'Settore',
    ],
    'views' => [
        'all' => 'Tutte le Aziende',
        'my' => 'Le Mie Aziende',
        'my_recently_assigned' => 'Le Mie Aziende Assegnate di Recente',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Il nome dell\'utente che ha assegnato l\'azienda',
    ],
    'workflows' => [
        'triggers' => [
            'created' => 'Azienda Creata',
        ],
        'actions' => [
            'fields' => [
                'email_to_company' => 'E-mail azienda',
                'email_to_owner_email' => 'E-mail del proprietario dell\'azienda',
                'email_to_creator_email' => 'E-mail del creatore dell\'azienda',
                'email_to_contact' => 'Contatto principale dell\'azienda',
            ],
        ],
    ],
    'validation' => [
        'email' => [
            'unique' => 'Esiste già un\'azienda con questa e-mail.',
        ],
    ],
    'empty_state' => [
        'title' => 'Non hai creato alcuna azienda.',
        'description' => 'Inizia creando una nuova azienda.',
    ],
];
