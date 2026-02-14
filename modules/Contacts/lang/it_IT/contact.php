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
    'contact' => 'Contatto',
    'contacts' => 'Contatti',
    'convert' => 'Converti in Contatto',
    'create' => 'Crea Contatto',
    'add' => 'Aggiungi Contatto',
    'total' => 'Totale Contatti',
    'import' => 'Importa Contatti',
    'export' => 'Esporta Contatti',
    'no_companies_associated' => 'Il contatto non ha aziende associate.',
    'no_deals_associated' => 'Il contatto non ha trattative associate.',
    'works_at' => ':job_title presso :company',
    'create_with' => 'Crea Contatto con :name',
    'associate_with' => 'Associa Contatto con :name',
    'associated_company' => 'Azienda associata al contatto',
    'dissociate' => 'Dissocia Contatto',
    'exists_in_trash_by_email' => 'Esiste già un contatto con questo indirizzo e-mail nel cestino, non sarà possibile creare un nuovo contatto con lo stesso indirizzo e-mail. Vuoi ripristinare il contatto eliminato?',
    'exists_in_trash_by_phone' => 'Il contatto (:contact) con i seguenti numeri: :phone_numbers, esiste già nel cestino. Vuoi ripristinare il contatto eliminato?',
    'possible_duplicate' => 'Possibile duplicato del contatto :display_name.',
    'associate_field_info' => 'Usa questo campo per trovare e associare un contatto esistente invece di crearne uno nuovo.',
    'cards' => [
        'recently_created' => 'Contatti creati di recente',
        'recently_created_info' => 'Mostrando gli ultimi :total contatti creati negli ultimi :days giorni, ordinati per più recenti in alto.',
        'by_day' => 'Contatti per giorno',
        'by_source' => 'Contatti per fonte',
    ],
    'count' => [
        'all' => '1 contatto | :count contatti',
    ],
    'notifications' => [
        'assigned' => 'Sei stato assegnato a un contatto :name da :user',
    ],
    'views' => [
        'all' => 'Tutti i Contatti',
        'my' => 'I Miei Contatti',
        'my_recently_assigned' => 'I Miei Contatti Assegnati di Recente',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Il nome dell\'utente che ha assegnato il contatto',
    ],
    'workflows' => [
        'triggers' => [
            'created' => 'Contatto Creato',
        ],
        'actions' => [
            'fields' => [
                'email_to_contact' => 'E-mail contatto',
                'email_to_owner_email' => 'E-mail del proprietario del contatto',
                'email_to_creator_email' => 'E-mail del creatore del contatto',
                'email_to_company' => 'Azienda primaria del contatto',
            ],
        ],
    ],
    'validation' => [
        'email' => [
            'unique' => 'Esiste già un contatto o un membro del team con questa e-mail.',
        ],
        'phone' => [
            'unique' => 'Esiste già un contatto con questo numero di telefono.',
        ],
    ],
    'empty_state' => [
        'title' => 'Non hai creato alcun contatto.',
        'description' => 'Inizia ora a organizzare le persone.',
    ],
];
