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
    'add' => 'Pridaj aktivitu',
    'description' => 'Popis',
    'description_info' => 'Popis je viditeľný pre všetkých účastníkov',
    'note' => 'Poznámka',
    'note_info' => 'Poznámky sú súkromné a viditeľné iba pre obchodných zástupcov.',
    'title' => 'Názov',
    'due_date' => 'Začiatok',
    'end_date' => 'Ukončenie',
    'create' => 'Vytvoriť aktivitu',
    'download_ics' => 'Stiahnuť .ics súbor',
    'created' => 'Aktivita bola úspešne vytvorená',
    'updated' => 'Aktivita bola úspešne aktualizovaná',
    'deleted' => 'Aktivita bola úspešne odstránená',
    'export' => 'Exportovať aktivity',
    'import' => 'Importovať aktivity',
    'guests' => 'Účastníci',
    'guest' => 'Účastník',
    'count_guests' => '1 Účastník| :count účastníkov',
    'create_follow_up_task' => 'Vytvoriť nadväzujúcu úlohu',
    'follow_up_with_title' => 'Pokračovať s :with',
    'title_via_create_message' => 'Re: :subject',
    'reminder_update_info' => 'Keďže pripomienka pre túto aktivitu je už odoslaná, budete musieť aktualizovať dátum platnosti, aby sa nová pripomienka zaradila do poradia.',
    'owner_assigned_date' => 'Dátum pridelený vlastníkom',
    'reminder_sent_date' => 'Dátum odoslania pripomienky',
    'reminder' => 'Pripomienka',
    'owner' => 'Vlastník',
    'mark_as_completed' => 'Označiť ako dokončené',
    'mark_as_incomplete' => 'Označiť ako nedokončené',
    'is_completed' => 'Dokončené',
    'completed_at' => 'Dokončené',
    'overdue' => 'Po termíne',
    'doesnt_have_activities' => 'Žiadne aktivity',
    'count' => 'Žiadne aktivity | 1 aktivita | :count aktivít',
    'incomplete_activities' => 'Nedokončené aktivity',
    'activity_was_due' => 'Dátum splnenia tejto aktivity bol :date',
    'next_activity_date' => 'Dátum nasledujúcej aktivity',
    'next_activity_date_info' => 'Toto pole je iba na čítanie a aktualizuje sa automaticky na základe zaznamenaných nadchádzajúcich aktivít, označuje, kedy by mal obchodný zástupca vykonať ďalšiu aktivitu.',
    'cards' => [
        'my_activities' => 'Moje aktivity',
        'my_activities_info' => 'Tieto karty zobrazujú aktivity, ku ktorým ste pridelení ako vlastník.',
        'created_by_agent' => 'Vytvorené aktivity',
        'created_by_agent_info' => 'Zobraziť počet aktivít vytvorených každým obchodným zástupcom. Zistite, kto vytvára najviac aktivít a kto vytvára najmenej.',
        'upcoming' => 'Nadchádzajúce aktivity',
        'upcoming_info' => 'Táto karta odzrkadľuje nadchádzajúce aktivity a aktivity, ktorým sa venujete.',
    ],
    'type' => [
        'default_type' => 'Predvolený typ aktivity',
        'delete_primary_warning' => 'Primárny typ aktivity nie je možné odstrániť.',
        'delete_usage_warning' => 'Typ je už priradený k aktivitám, preto ho nemožno odstrániť.',
        'delete_usage_calendars_warning' => 'Tento typ sa používa ako predvolený typ pri vytváraní aktivít prostredníctvom pripojených kalendárov, preto ho nemožno odstrániť.',
        'delete_is_default' => 'Toto je predvolený typ aktivity, preto ho nemožno odstrániť.',
        'type' => 'Typ aktivity',
        'types' => 'Typy aktivít',
        'name' => 'Názov',
        'icon' => 'Ikona',
    ],
    'views' => [
        'all' => 'Všetky aktivity',
        'open' => 'Aktívne aktivity',
        'due_today' => 'Aktivity na dnes',
        'due_this_week' => 'Aktivity na tento týždeň',
        'overdue' => 'Oneskorené aktivity',
    ],
    'filters' => [
        'display' => [
            'has' => 'máte :value: aktivít',
            'overdue' => 'má :value: aktivít',
            'doesnt_have_activities' => 'nevykonáva žiadne aktivity',
        ],
        'all' => 'Všetky',
        'today' => 'Dnes',
        'tomorrow' => 'Zajtra',
        'this_week' => 'Tento týždeň',
        'next_week' => 'Budúci týždeň',
        'done' => 'Hotovo',
        'done_empty_state' => 'Tu sa zobrazia vykonané aktivity.',
    ],
    'settings' => [
        'send_contact_email' => 'Odoslať e-mailovú šablónu "Contact attends to activity" kontaktom.',
        'send_contact_email_info' => 'Ak je povolené, pri pridávaní kontaktu účastníka na aktivitu bude odoslaná e-mailová šablóna s priloženým súborom .ics a informáciami o aktivite.',
        'add_event_guests_to_contacts' => 'Pridať synchronizovaných účastníkov udalosti do kontaktov',
        'add_event_guests_to_contacts_info' => 'Keď je táto možnosť povolená, účastníci zo synchronizovaných udalostí, ktorí ešte nie sú kontaktmi, budú pridaní ako nové kontakty.',
    ],
    'manage_activities' => 'Spravovať aktivity',
    'info' => 'Plánujte a spravujte aktivity s kontaktmi a obchodnými zástupcami.',
    'timeline' => [
        'heading' => 'Aktivita bola vytvorená',
    ],
    'permissions' => [
        'attends_and_owned' => 'Iba priradená a vlastné',
    ],
    'actions' => [
        'update_type' => 'Typ aktualizácie',
    ],
    'notifications' => [
        'due' => 'Vaša :activity aktivita je naplánovaná na :date.',
        'assigned' => 'Boli ste priradený k aktivite :name používateľom :user.',
        'added_as_guest' => 'Boli ste pridaný do aktivity ako účastník',
    ],
    'mail_placeholders' => [
        'assigneer' => 'Meno používateľa, ktorý priradil aktivitu.',
    ],
    'validation' => [
        'end_date' => [
            'less_than_due' => 'Dátum ukončenia nesmie byť starší ako dátum vytvorenia.',
        ],
        'end_time' => [
            'required_when_end_date_is_in_future' => 'Musíte zadať čas ukončenia, ak je dátum ukončenia v iný deň.',
        ],
    ],
    'workflows' => [
        'actions' => [
            'create' => 'Vytvoriť aktivitu',
        ],
        'fields' => [
            'create' => [
                'auto_associate' => 'Automaticky priradiť aktivitu ku všetkým dostupným asociáciám?',
                'auto_associate_info' => 'Napríklad: priraďte aktivitu k všetkým firmám a kontaktom novovytvorenej firmy.',
                'title' => 'S názvom aktivity',
                'note' => 'Pridať poznámku (voliteľné)',
            ],
        ],
    ],
    'metrics' => [
        'todays' => 'Dnešné aktivity',
    ],
    'empty_state' => [
        'title' => 'Nevytvorili ste žiadne aktivity.',
        'description' => 'Začnite vytvorením novej aktivity.',
    ],
];
