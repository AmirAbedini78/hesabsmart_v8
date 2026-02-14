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
    'settings' => 'Impostazioni',
    'updated' => 'Impostazioni Aggiornate',
    'general_settings' => 'Impostazioni Generali',
    'company_information' => 'Informazioni Azienda',
    'update_user_account_info' => 'Aggiornare queste impostazioni non influirà sulle impostazioni del tuo account utente poiché queste sono generali. Aggiorna le stesse impostazioni nel tuo account utente se desideri modificare queste opzioni.',
    'general' => 'Generale',
    'system' => 'Sistema',
    'system_email' => 'Account E-mail di Sistema',
    'system_email_configured' => 'Account configurato da un altro utente',
    'system_email_info' => 'Seleziona l\'account e-mail collegato all\'Inbox che sarà utilizzato per inviare e-mail relative al sistema, come l\'assegnazione di un utente a un contatto, promemoria attività in scadenza, inviti utente, ecc...',
    'choose_logo' => 'Scegli Logo',
    'date_format' => 'Formato Data',
    'time_format' => 'Formato Ora',
    'go_to_settings' => 'Vai alle impostazioni',
    'privacy_policy_info' => 'Se non hai una politica sulla privacy, puoi configurarne una qui. Visualizza la politica sulla privacy al seguente URL: :url',
    'phones' => [
        'require_calling_prefix' => 'Richiedi prefisso telefonico sui numeri di telefono',
        'require_calling_prefix_info' => 'La maggior parte delle integrazioni di chiamata richiede che i numeri di telefono siano nel formato E.164. Abilitare questa opzione garantirà che nessun numero di telefono venga inserito senza un prefisso di chiamata specifico per il paese.',
    ],
    'recaptcha' => [
        'recaptcha' => 'reCaptcha',
        'site_key' => 'Chiave del Sito',
        'secret_key' => 'Chiave Segreta',
        'ignored_ips' => 'Indirizzi IP Ignorati',
        'ignored_ips_info' => 'Inserisci gli indirizzi IP separati da virgola che vuoi escludere dalla validazione reCaptcha.',
        'dont_get_locked' => 'Non rimanere bloccato',
        'ensure_recaptcha_works' => 'Per garantire che la configurazione di reCaptcha funzioni correttamente, esegui sempre un test di login in Modalità Incognito mantenendo attiva la finestra corrente.',
    ],
    'security' => [
        'security' => 'Sicurezza',
        'disable_password_forgot' => 'Disabilita la funzione "password dimenticata"',
        'disable_password_forgot_info' => 'Se abilitato, la funzione "password dimenticata" sarà disabilitata.',
        'block_bad_visitors' => 'Blocca visitatori malevoli',
        'block_bad_visitors_info' => 'Se abilitato, per ogni visitatore ospite verrà controllata una lista di user agent, indirizzi IP e referrer non autorizzati.',
    ],
    'tools' => [
        'tools' => 'Strumenti',
        'run' => 'Esegui Strumento',
        'executed' => 'Azione eseguita con successo',
        'clear-cache' => 'Cancella la cache dell\'applicazione.',
        'storage-link' => 'Crea un collegamento simbolico da "public/storage" a "storage/app/public".',
        'optimize' => 'Cache dei file bootstrap dell\'applicazione come configurazioni e rotte.',
        'seed-mailable-templates' => 'Popola i template e-mail dell\'applicazione.',
    ],
];
