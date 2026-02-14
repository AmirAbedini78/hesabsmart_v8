<?php

/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'contact' => 'مخاطب',
    'contacts' => 'مخاطبان',
    'convert' => 'تبدیل به مخاطب',
    'create' => 'ایجاد مخاطب',
    'add' => 'افزودن مخاطب',
    'total' => 'کل مخاطبان',
    'import' => 'ورود مخاطبان',
    'export' => 'خروجی مخاطبان',
    'no_companies_associated' => 'این مخاطب شرکتی ندارد.',
    'no_deals_associated' => 'این مخاطب معامله‌ای ندارد.',
    'works_at' => ':job_title در :company',
    'create_with' => 'ایجاد مخاطب با :name',
    'associate_with' => 'ارتباط مخاطب با :name',
    'associated_company' => 'شرکت مرتبط با مخاطب',
    'dissociate' => 'حذف ارتباط مخاطب',

    'exists_in_trash_by_email' => 'مخاطبی با این ایمیل در سطل بازیافت وجود دارد. برای ایجاد مخاطب جدید با همین ایمیل، ابتدا مخاطب حذف‌شده را بازیابی کنید؟',

    'exists_in_trash_by_phone' => 'مخاطب (:contact) با این شماره‌ها: :phone_numbers در سطل بازیافت وجود دارد. بازیابی شود؟',

    'possible_duplicate' => 'مخاطب تکراری احتمالی: :display_name.',

    'associate_field_info' => 'از این فیلد برای یافتن و ارتباط مخاطب موجود به‌جای ایجاد جدید استفاده کنید.',

    'cards' => [
        'recently_created' => 'مخاطبان ایجاد شده اخیر',
        'recently_created_info' => 'نمایش آخرین :total مخاطب ایجاد شده در :days روز گذشته.',
        'by_day' => 'مخاطبان به تفکیک روز',
        'by_source' => 'مخاطبان به تفکیک منبع',
    ],

    'count' => [
        'all' => '۱ مخاطب | :count مخاطب',
    ],

    'notifications' => [
        'assigned' => 'مخاطب :name توسط :user به شما تخصیص داده شد',
    ],

    'views' => [
        'all' => 'همه مخاطبان',
        'my' => 'مخاطبان من',
        'my_recently_assigned' => 'مخاطبان تخصیص‌یافته اخیر من',
    ],

    'mail_placeholders' => [
        'assigneer' => 'نام کاربری که مخاطب را تخصیص داده',
    ],

    'workflows' => [
        'triggers' => [
            'created' => 'مخاطب ایجاد شد',
        ],
        'actions' => [
            'fields' => [
                'email_to_contact' => 'ایمیل مخاطب',
                'email_to_owner_email' => 'ایمیل مالک مخاطب',
                'email_to_creator_email' => 'ایمیل ایجادکننده مخاطب',
                'email_to_company' => 'شرکت اصلی مخاطب',
            ],
        ],
    ],

    'validation' => [
        'email' => [
            'unique' => 'مخاطب یا عضو تیمی با این ایمیل از قبل وجود دارد.',
        ],
        'phone' => [
            'unique' => 'مخاطبی با این شماره تلفن از قبل وجود دارد.',
        ],
    ],

    'empty_state' => [
        'title' => 'هنوز مخاطبی ایجاد نکرده‌اید.',
        'description' => 'از همین حالا افراد را سازماندهی کنید.',
    ],
];
