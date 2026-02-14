<?php

/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'company' => 'شرکت',
    'companies' => 'شرکت‌ها',
    'add' => 'افزودن شرکت',
    'dissociate' => 'حذف ارتباط شرکت',
    'child' => 'شرکت فرعی',
    'create' => 'ایجاد شرکت',
    'export' => 'خروجی شرکت‌ها',
    'total' => 'کل شرکت‌ها',
    'import' => 'ورود شرکت‌ها',
    'create_with' => 'ایجاد شرکت با :name',
    'associate_with' => 'ارتباط شرکت با :name',
    'associate_field_info' => 'از این فیلد برای یافتن و ارتباط شرکت موجود به‌جای ایجاد جدید استفاده کنید.',
    'no_contacts_associated' => 'این شرکت مخاطبی ندارد.',
    'no_deals_associated' => 'این شرکت معامله‌ای ندارد.',

    'exists_in_trash_by_email' => 'شرکتی با این ایمیل در سطل بازیافت وجود دارد. بازیابی شود؟',

    'exists_in_trash_by_name' => 'شرکتی با همین نام در سطل بازیافت وجود دارد. بازیابی شود؟',

    'exists_in_trash_by_phone' => 'شرکت (:company) با این شماره‌ها: :phone_numbers در سطل بازیافت وجود دارد. بازیابی شود؟',

    'possible_duplicate' => 'شرکت تکراری احتمالی: :display_name.',

    'count' => [
        'all' => '۱ شرکت | :count شرکت',
    ],

    'notifications' => [
        'assigned' => 'شرکت :name توسط :user به شما تخصیص داده شد',
    ],

    'cards' => [
        'by_source' => 'شرکت‌ها به تفکیک منبع',
        'by_day' => 'شرکت‌ها به تفکیک روز',
    ],

    'settings' => [
        'automatically_associate_with_contacts' => 'ایجاد و ارتباط خودکار شرکت‌ها با مخاطبان',
        'automatically_associate_with_contacts_info' => 'ارتباط خودکار مخاطبان با شرکت‌ها بر اساس دامنه ایمیل مخاطب و دامنه شرکت.',
    ],

    'industry' => [
        'industries' => 'صنایع',
        'industry' => 'صنعت',
    ],

    'views' => [
        'all' => 'همه شرکت‌ها',
        'my' => 'شرکت‌های من',
        'my_recently_assigned' => 'شرکت‌های تخصیص‌یافته اخیر من',
    ],

    'mail_placeholders' => [
        'assigneer' => 'نام کاربری که شرکت را تخصیص داده',
    ],

    'workflows' => [
        'triggers' => [
            'created' => 'شرکت ایجاد شد',
        ],
        'actions' => [
            'fields' => [
                'email_to_company' => 'ایمیل شرکت',
                'email_to_owner_email' => 'ایمیل مالک شرکت',
                'email_to_creator_email' => 'ایمیل ایجادکننده شرکت',
                'email_to_contact' => 'مخاطب اصلی شرکت',
            ],
        ],
    ],

    'validation' => [
        'email' => [
            'unique' => 'شرکتی با این ایمیل از قبل وجود دارد.',
        ],
    ],

    'empty_state' => [
        'title' => 'هنوز شرکتی ایجاد نکرده‌اید.',
        'description' => 'با ایجاد یک شرکت جدید شروع کنید.',
    ],
];
