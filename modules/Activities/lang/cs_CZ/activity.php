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
    'activities' => 'Aktivity',
    'activity' => 'Aktivita',
    'add' => 'Přidej aktivitu',
    'description' => 'Popis',
    'description_info' => 'Popis je viditelný pro všechny účastníky',
    'note' => 'Poznámka',
    'note_info' => 'Poznámky jsou soukromé a viditelné pouze pro obchodní zástupce.',
    'title' => 'Název',
    'due_date' => 'Aktivní od',
    'end_date' => 'Datum ukončení',
    'create' => 'Vytvořit aktivitu',
    'download_ics' => 'Stáhnout .ics soubor',
    'created' => 'Aktivita byla úspěšně vytvořena',
    'updated' => 'Aktivita byla úspěšně aktualizována',
    'deleted' => 'Aktivita byla úspěšně odstraněna',
    'export' => 'Exportovat aktivity',
    'import' => 'Importovat aktivity',
    'guests' => 'Účastníci',
    'guest' => 'Účastník',
    'count_guests' => '1 Účastník| :count účastníků',
    'create_follow_up_task' => 'Vytvořit navazující úlohu',
    'follow_up_with_title' => 'Pokračovat s :with',
    'title_via_create_message' => 'Re: :subject',
    'reminder_update_info' => 'Protože připomínka pro tuto aktivitu je již odeslána, budete muset aktualizovat datum splatnosti, aby se nová připomínka zařadila do pořadí.',
    'owner_assigned_date' => 'Datum přidělené vlastníkem',
    'reminder_sent_date' => 'Datum odeslání připomínky',
    'reminder' => 'Připomínka',
    'owner' => 'Vlastník',
    'mark_as_completed' => 'Označit jako dokončené',
    'mark_as_incomplete' => 'Označit jako nedokončené',
    'is_completed' => 'Dokončeno',
    'completed_at' => 'Dokončeno',
    'overdue' => 'Po termínu',
    'doesnt_have_activities' => 'Žádné aktivity',
    'count' => 'Žádné aktivity | 1 aktivita | :count aktivit',
    'incomplete_activities' => 'Nedokončené aktivity',
    'activity_was_due' => 'Datum splnění této aktivity bylo :date',
    'next_activity_date' => 'Datum následující aktivity',
    'next_activity_date_info' => 'Toto pole je pouze pro čtení a aktualizuje se automaticky na základě zaznamenaných nadcházejících aktivit, označuje, kdy by měl obchodní zástupce provést další aktivitu.',
    'cards' => [
        'my_activities' => 'Mé aktivity',
        'my_activities_info' => 'Tyto karty zobrazují aktivity, ke kterým jste přiděleni jako vlastník.',
        'created_by_agent' => 'Vytvořené aktivity',
        'created_by_agent_info' => 'Zobrazit počet aktivit vytvořených každým obchodním zástupcem. Zjistěte, kdo vytváří nejvíce aktivit a kdo vytváří nejméně.',
        'upcoming' => 'Nadcházející aktivity',
        'upcoming_info' => 'Tato karta odráží nadcházející aktivity a aktivity, kterým se věnujete.',
    ],
    'type' => [
        'default_type' => 'Výchozí typ aktivity',
        'delete_primary_warning' => 'Primární typ aktivity nelze odstranit.',
        'delete_usage_warning' => 'Typ je již přiřazen k aktivitám, proto jej nelze odstranit.',
        'delete_usage_calendars_warning' => 'Tento typ se používá jako výchozí typ při vytváření aktivit prostřednictvím připojených kalendářů, proto jej nelze odstranit.',
        'delete_is_default' => 'Toto je výchozí typ aktivity, proto jej nelze odstranit.',
        'type' => 'Typ aktivity',
        'types' => 'Typy aktivit',
        'name' => 'Název',
        'icon' => 'Ikona',
    ],
    'views' => [
        'all' => 'Všechny aktivity',
        'open' => 'Aktivní aktivity',
        'due_today' => 'Aktivity na dnes',
        'due_this_week' => 'Aktivity na tento týden',
        'overdue' => 'Opožděné aktivity',
    ],
    'filters' => [
        'display' => [
            'has' => 'máte :value: aktivit',
            'overdue' => 'má :value: aktivit',
            'doesnt_have_activities' => 'neprovádí žádné aktivity',
        ],
        'all' => 'Všechny',
        'today' => 'Dnes',
        'tomorrow' => 'Zítra',
        'this_week' => 'Tento týden',
        'next_week' => 'Příští týden',
        'done' => 'Hotovo',
        'done_empty_state' => 'Zde se zobrazí provedené aktivity.',
    ],
    'settings' => [
        'send_contact_email' => 'Odeslat e-mailovou šablonu "Contact attends to activity" kontaktem.',
        'send_contact_email_info' => 'Pokud je povoleno, při přidávání kontaktu účastníka na aktivitu bude odeslána e-mailová šablona s přiloženým souborem .ics a informacemi o aktivitě.',
        'add_event_guests_to_contacts' => 'Přidat synchronizované účastníky události do kontaktů',
        'add_event_guests_to_contacts_info' => 'Když je tato možnost povolena, účastníci ze synchronizovaných událostí, kteří ještě nejsou kontakty, budou přidáni jako nové kontakty.',
    ],
    'manage_activities' => 'Spravovat aktivity',
    'info' => 'Plánujte a spravujte aktivity s kontakty a obchodními zástupci.',
    'timeline' => [
        'heading' => 'Aktivita byla vytvořena',
    ],
    'permissions' => [
        'attends_and_owned' => 'Pouze přiřazena a vlastní',
    ],
    'actions' => [
        'update_type' => 'Typ aktualizace',
    ],
    'notifications' => [
        'due' => 'Vaše :activity aktivita je naplánována na :date.',
        'assigned' => 'Byli jste přiřazeni k aktivitě :name uživatelem :user.',
        'added_as_guest' => 'Byli jste přidáni do aktivity jako účastník',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Uživatelské jméno, který přiřadil aktivitu.',
    ],
    'validation' => [
        'end_date' => [
            'less_than_due' => 'Datum ukončení nesmí být starší než datum vytvoření.',
        ],
        'end_time' => [
            'required_when_end_date_is_in_future' => 'Musíte zadat čas ukončení, pokud je datum ukončení v jiný den.',
        ],
    ],
    'workflows' => [
        'actions' => [
            'create' => 'Vytvořit aktivitu',
        ],
        'fields' => [
            'create' => [
                'auto_associate' => 'Automaticky přiřadit aktivitu ke všem dostupným asociacím?',
                'auto_associate_info' => 'Například: přiřaďte aktivitu ke všem firmám a kontaktům nově vytvořené firmy.',
                'title' => 'S názvem aktivity',
                'note' => 'Přidat poznámku (volitelné)',
            ],
        ],
    ],
    'metrics' => [
        'todays' => 'Dnešní aktivity',
    ],
    'empty_state' => [
        'title' => 'Nevytvořili jste žádné aktivity.',
        'description' => 'Začněte vytvořením nové aktivity.',
    ],
];
