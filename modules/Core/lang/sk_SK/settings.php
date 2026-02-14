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
    'settings' => 'Nastavenia',
    'updated' => 'Nastavenia aktualizované',
    'general_settings' => 'Všeobecné nastavenia',
    'company_information' => 'Informácie o spoločnosti',
    'update_user_account_info' => 'Aktualizácia týchto nastavení neovplyvní nastavenia vášho používateľského účtu, pretože tieto nastavenia sú všeobecné. Ak chcete aktualizovať tieto možnosti, aktualizujte namiesto toho rovnaké nastavenia vo svojom používateľskom účte.',
    'general' => 'Všeobecné',
    'system' => 'Systém',
    'system_email' => 'Systémový e-mailový účet',
    'system_email_configured' => 'Účet nakonfigurovaný iným používateľom',
    'system_email_info' => 'Vyberte e-mailový účet pripojený E-mailovému klientovi, ktorý sa bude používať na odosielanie e-mailov súvisiacich so systémom, ako je používateľ priradený ku kontaktu, pripomenutie termínu aktivity, pozvánky používateľov atď...',
    'choose_logo' => 'Vybrať logo',
    'date_format' => 'Formát dátumu',
    'time_format' => 'Formát času',
    'go_to_settings' => 'Prejsť na nastavenia',
    'privacy_policy_info' => 'Ak nemáte zásady ochrany osobných údajov, môžete si ich nakonfigurovať tu, pozrite si zásady ochrany osobných údajov na nasledujúcej adrese URL: :url',
    'phones' => [
        'require_calling_prefix' => 'Pri telefónnych číslach vyžadovať predvoľbu',
        'require_calling_prefix_info' => 'Väčšina integrácií hovorov vyžaduje, aby telefónne čísla boli vo formáte E.164. Povolením tejto možnosti zaistíte, že nebudú zadané žiadne telefónne čísla bez predvoľby špecifickej pre danú krajinu.',
    ],
    'recaptcha' => [
        'recaptcha' => 'reCaptcha',
        'site_key' => 'Kľúč stránky',
        'secret_key' => 'Tajný kľúč',
        'ignored_ips' => 'Ignorované IP adresy',
        'ignored_ips_info' => 'Zadajte IP adresy oddelené čiarkou, pre ktoré chcete, aby reCaptcha preskočila overenie.',
        'dont_get_locked' => 'Zakázať režim blokácie',
        'ensure_recaptcha_works' => 'Pre zabezpečenie správneho fungovania konfigurácie reCaptcha vždy vykonajte testovacie prihlásenie cez režim Inkognito, pričom ponecháte aktívne súčasné okno.',
    ],
    'security' => [
        'security' => 'Zabezpečenie',
        'disable_password_forgot' => 'Deaktivovať funkciu Zabudnuté heslo',
        'disable_password_forgot_info' => 'Keď je povolené, funkcia zabudnutého hesla bude zakázaná. Užívatelia ktorý zabudnú heslo si ho nebudú môcť sami obnoviť.',
        'block_bad_visitors' => 'Blokovať nežiaduce návštevy',
        'block_bad_visitors_info' => 'Aktivovaním tejto funkcie sa návštevy hostí porovnávajú so zoznamom známych zloduchov používateľských agentov, IP adries a refererov.',
    ],
    'tools' => [
        'tools' => 'Nástroje',
        'run' => 'Spustiť nástroj',
        'executed' => 'Akcia bola úspešne vykonaná',
        'clear-cache' => 'Vymazať vyrovnávaciu pamäť aplikácie',
        'storage-link' => 'Vytvoriť symbolický odkaz z „public/storage“ na „storage/app/public“.',
        'optimize' => 'Uložiť do vyrovnávacej pamäte zavádzacie súbory aplikácie, ako sú config a routes.',
        'seed-mailable-templates' => 'Umiestnite šablóny aplikácie na odoslanie.',
    ],
];
