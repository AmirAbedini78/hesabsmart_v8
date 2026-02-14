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
    'activities' => 'Attività',
    'activity' => 'Attività',
    'add' => 'Aggiungi Attività',
    'description' => 'Descrizione',
    'description_info' => 'La descrizione è visibile a tutti gli ospiti',
    'note' => 'Nota',
    'note_info' => 'Le note sono private e visibili solo per i rappresentanti di vendita.',
    'title' => 'Titolo',
    'due_date' => 'Data di Scadenza',
    'end_date' => 'Data di Fine',
    'create' => 'Crea Attività',
    'download_ics' => 'Scarica file .ics',
    'created' => 'Attività creata con successo',
    'updated' => 'Attività aggiornata con successo',
    'deleted' => 'Attività eliminata con successo',
    'export' => 'Esporta Attività',
    'import' => 'Importa Attività',
    'guests' => 'Ospiti',
    'guest' => 'Ospite',
    'count_guests' => '1 ospite | :count ospiti',
    'create_follow_up_task' => 'Crea attività di follow-up',
    'follow_up_with_title' => 'Follow-up con :with',
    'title_via_create_message' => 'Riguardo a un\'e-mail: :subject',
    'reminder_update_info' => 'Poiché il promemoria per questa attività è già stato inviato, è necessario aggiornare la data di scadenza per inviare un nuovo promemoria in coda.',
    'owner_assigned_date' => 'Data di Assegnazione del Proprietario',
    'reminder_sent_date' => 'Data di Invio del Promemoria',
    'reminder' => 'Promemoria',
    'owner' => 'Proprietario',
    'mark_as_completed' => 'Segna come completata',
    'mark_as_incomplete' => 'Segna come incompleta',
    'is_completed' => 'Completata',
    'completed_at' => 'Completata il',
    'overdue' => 'In Ritardo',
    'doesnt_have_activities' => 'Nessuna Attività',
    'count' => 'Nessuna Attività | 1 Attività | :count Attività',
    'incomplete_activities' => 'Attività Incompiute',
    'activity_was_due' => 'Questa attività era prevista per il :date',
    'next_activity_date' => 'Data della Prossima Attività',
    'next_activity_date_info' => 'Questo campo è di sola lettura e viene aggiornato automaticamente in base alle prossime attività del record, indicando quando deve essere intrapresa la prossima azione del rappresentante di vendita.',
    'cards' => [
        'my_activities' => 'Le mie attività',
        'my_activities_info' => 'Questa scheda riflette le attività a cui sei aggiunto come proprietario',
        'created_by_agent' => 'Attività create dall\'agente di vendita',
        'created_by_agent_info' => 'Visualizza il numero di attività create da ogni agente di vendita. Vedi chi sta creando più attività e chi meno.',
        'upcoming' => 'Prossime attività',
        'upcoming_info' => 'Questa scheda riflette le attività imminenti a cui stai partecipando.',
    ],
    'type' => [
        'default_type' => 'Tipo di Attività Predefinito',
        'delete_primary_warning' => 'Non puoi eliminare il tipo di attività principale.',
        'delete_usage_warning' => 'Il tipo è già associato ad attività, quindi non può essere eliminato.',
        'delete_usage_calendars_warning' => 'Questo tipo è usato come tipo predefinito durante la creazione di attività tramite calendari collegati, quindi non può essere eliminato.',
        'delete_is_default' => 'Questo è un tipo di attività predefinito, quindi non può essere eliminato.',
        'type' => 'Tipo di Attività',
        'types' => 'Tipi di Attività',
        'name' => 'Nome',
        'icon' => 'Icona',
    ],
    'views' => [
        'all' => 'Tutte le Attività',
        'open' => 'Attività Aperte',
        'due_today' => 'Attività in Scadenza Oggi',
        'due_this_week' => 'Attività in Scadenza Questa Settimana',
        'overdue' => 'Attività in Ritardo',
    ],
    'filters' => [
        'display' => [
            'has' => 'ha attività :value:',
            'overdue' => 'ha :value: attività',
            'doesnt_have_activities' => 'non ha attività',
        ],
        'all' => 'Tutte',
        'today' => 'Oggi',
        'tomorrow' => 'Domani',
        'this_week' => 'Questa Settimana',
        'next_week' => 'Prossima Settimana',
        'done' => 'Fatto',
        'done_empty_state' => 'Le attività completate verranno mostrate qui.',
    ],
    'settings' => [
        'send_contact_email' => 'Invia il modello di e-mail "Il contatto partecipa all\'attività" ai contatti',
        'send_contact_email_info' => 'Se abilitato, quando un contatto è aggiunto come ospite a un\'attività, verrà inviato un modello di e-mail con allegato il file .ics e le informazioni dell\'attività.',
        'add_event_guests_to_contacts' => 'Aggiungi gli ospiti degli eventi sincronizzati ai contatti',
        'add_event_guests_to_contacts_info' => 'Se abilitato, gli ospiti degli eventi sincronizzati che non sono già contatti verranno aggiunti come nuovi contatti.',
    ],
    'manage_activities' => 'Gestisci Attività',
    'info' => 'Pianifica e gestisci attività con contatti e rappresentanti di vendita.',
    'timeline' => [
        'heading' => 'Un\'attività è stata creata',
    ],
    'permissions' => [
        'attends_and_owned' => 'Partecipa e possiede solo',
    ],
    'actions' => [
        'update_type' => 'Aggiorna tipo',
    ],
    'notifications' => [
        'due' => 'La tua attività :activity scade il :date',
        'assigned' => 'Sei stato assegnato all\'attività :name da :user',
        'added_as_guest' => 'Sei stato aggiunto come ospite all\'attività',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Il nome dell\'utente che ha assegnato l\'attività',
    ],
    'validation' => [
        'end_date' => [
            'less_than_due' => 'La data di fine non deve essere precedente alla data di scadenza.',
        ],
        'end_time' => [
            'required_when_end_date_is_in_future' => 'Devi specificare l\'ora di fine quando la data di fine è in un giorno diverso.',
        ],
    ],
    'workflows' => [
        'actions' => [
            'create' => 'Crea Attività',
        ],
        'fields' => [
            'create' => [
                'auto_associate' => 'Associare automaticamente l\'attività a tutte le associazioni disponibili?',
                'auto_associate_info' => 'Ad esempio: associa l\'attività a tutte le trattative e i contatti di un\'azienda appena creata.',
                'title' => 'Con titolo dell\'attività',
                'note' => 'Aggiungi una nota (opzionale)',
            ],
        ],
    ],
    'metrics' => [
        'todays' => 'Attività di Oggi',
    ],
    'empty_state' => [
        'title' => 'Non hai creato alcuna attività.',
        'description' => 'Inizia creando una nuova attività.',
    ],
];
