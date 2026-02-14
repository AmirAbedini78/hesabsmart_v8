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
    'settings' => 'Nastavení',
    'updated' => 'Nastavení aktualizováno',
    'general_settings' => 'Všeobecná nastavení',
    'company_information' => 'Informace o společnosti',
    'update_user_account_info' => 'Aktualizace těchto nastavení neovlivní nastavení vašeho uživatelského účtu, protože tato nastavení jsou obecná. Chcete-li aktualizovat tyto možnosti, aktualizujte místo toho stejná nastavení ve svém uživatelském účtu.',
    'general' => 'Obecné',
    'system' => 'Systém',
    'system_email' => 'Systémový e-mailový účet',
    'system_email_configured' => 'Účet nakonfigurován jiným uživatelem',
    'system_email_info' => 'Vyberte e-mailový účet připojený E-mailovému klientovi, který se bude používat k odesílání e-mailů souvisejících se systémem, jako je uživatel přiřazený ke kontaktu, připomenutí termínu aktivity, pozvánky uživatelů atd...',
    'choose_logo' => 'Vybrat logo',
    'date_format' => 'Formát data',
    'time_format' => 'Formát času',
    'go_to_settings' => 'Přejít na nastavení',
    'privacy_policy_info' => 'Pokud nemáte zásady ochrany osobních údajů, můžete si je nakonfigurovat zde, podívejte se na zásady ochrany osobních údajů na následující adrese URL: :url',
    'phones' => [
        'require_calling_prefix' => 'U telefonních číslů vyžadovat předvolbu',
        'require_calling_prefix_info' => 'Většina integrací hovorů vyžaduje, aby telefonní čísla byla ve formátu E.164. Povolením této možnosti zajistíte, že nebudou zadána žádná telefonní čísla bez předvolby specifické pro danou zemi.',
    ],
    'recaptcha' => [
        'recaptcha' => 'reCaptcha',
        'site_key' => 'Klíč stránky',
        'secret_key' => 'Tajný klíč',
        'ignored_ips' => 'Ignorované IP adresy',
        'ignored_ips_info' => 'Zadejte IP adresy oddělené čárkou, pro které chcete, aby reCaptcha přeskočila ověření.',
        'dont_get_locked' => 'Zakázat režim blokace',
        'ensure_recaptcha_works' => 'Pro zajištění správného fungování konfigurace reCaptcha vždy proveďte testovací přihlášení přes režim Inkognito, přičemž ponecháte aktivní současné okno.',
    ],
    'security' => [
        'security' => 'Zabezpečení',
        'disable_password_forgot' => 'Deaktivovat funkci Zapomenuté heslo',
        'disable_password_forgot_info' => 'Když je povoleno, funkce zapomenutého hesla bude zakázána. Uživatelé, kteří zapomenou heslo, si ho nebudou moci sami obnovit.',
        'block_bad_visitors' => 'Blokovat nežádoucí návštěvy',
        'block_bad_visitors_info' => 'Aktivováním této funkce se návštěvy hostů porovnávají se seznamem známých padouchů uživatelských agentů, IP adres a refererů.',
    ],
    'tools' => [
        'tools' => 'Nástroje',
        'run' => 'Spustit nástroj',
        'executed' => 'Akce byla úspěšně provedena',
        'clear-cache' => 'Vymazat vyrovnávací paměť aplikace',
        'storage-link' => 'Vytvořit symbolický odkaz z „public/storage“ na „storage/app/public“.',
        'optimize' => 'Uložit do vyrovnávací paměti zaváděcí soubory aplikace, jako jsou config a routes.',
        'seed-mailable-templates' => 'Umístěte šablony aplikace k odeslání.',
    ],
];
