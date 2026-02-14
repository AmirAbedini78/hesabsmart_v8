<?php

/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'settings' => 'تنظیمات',
    'updated' => 'تنظیمات به‌روزرسانی شد',
    'general_settings' => 'تنظیمات عمومی',
    'company_information' => 'اطلاعات شرکت',
    'update_user_account_info' => 'به‌روزرسانی این تنظیمات روی تنظیمات حساب کاربری شما اثر نمی‌گذارد؛ برای تغییر این گزینه‌ها همان موارد را در حساب کاربری به‌روز کنید.',
    'general' => 'عمومی',
    'system' => 'سیستم',
    'system_email' => 'حساب ایمیل سیستم',
    'system_email_configured' => 'حساب توسط کاربر دیگر پیکربندی شده',
    'system_email_info' => 'حساب ایمیل متصل به صندوق ورودی را انتخاب کنید که برای ارسال ایمیل‌های سیستمی (مثل تخصیص به مخاطب، یادآوری فعالیت، دعوت کاربر و...) استفاده شود.',
    'choose_logo' => 'انتخاب لوگو',
    'date_format' => 'قالب تاریخ',
    'time_format' => 'قالب زمان',
    'go_to_settings' => 'رفتن به تنظیمات',

    'privacy_policy_info' => 'اگر صفحه حریم خصوصی ندارید، می‌توانید اینجا تنظیم کنید. آدرس: :url',

    'phones' => [
        'require_calling_prefix' => 'الزام پیش‌شماره تماس در شماره تلفن',
        'require_calling_prefix_info' => 'بیشتر یکپارچه‌سازی‌های تماس شماره را به فرمت E.164 می‌خواهند. با فعال بودن این گزینه، شماره بدون پیش‌شماره کشور قابل ورود نیست.',
    ],

    'recaptcha' => [
        'recaptcha' => 'reCaptcha',
        'site_key' => 'کلید سایت',
        'secret_key' => 'کلید مخفی',
        'ignored_ips' => 'آدرس‌های IP نادیده',
        'ignored_ips_info' => 'آدرس‌های IP جدا شده با کاما که می‌خواهید reCaptcha از آن‌ها صرف‌نظر کند وارد کنید.',
        'dont_get_locked' => 'قفل نشوید',
        'ensure_recaptcha_works' => 'برای اطمینان از کارکرد reCaptcha، یک بار ورود از حالت ناشناس را با باز بودن همین پنجره تست کنید.',
    ],

    'security' => [
        'security' => 'امنیت',
        'disable_password_forgot' => 'غیرفعال کردن فراموشی رمز عبور',
        'disable_password_forgot_info' => 'با فعال شدن، امکان فراموشی رمز عبور غیرفعال می‌شود.',
        'block_bad_visitors' => 'مسدود کردن بازدیدکنندگان مخرب',
        'block_bad_visitors_info' => 'در صورت فعال بودن، لیست user agentها، IPها و مراجع نامعتبر برای هر مهمان بررسی می‌شود.',
    ],

    'tools' => [
        'tools' => 'ابزارها',
        'run' => 'اجرای ابزار',
        'executed' => 'اقدام با موفقیت انجام شد',

        'clear-cache' => 'پاک کردن کش برنامه.',
        'storage-link' => 'ایجاد لینک نمادین از "public/storage" به "storage/app/public".',
        'optimize' => 'کش کردن فایل‌های bootstrap برنامه (config و routes).',
        'seed-mailable-templates' => 'بارگذاری قالب‌های ایمیل برنامه.',
    ],
];
