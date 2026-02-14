<?php

/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'create' => 'ایجاد گردش کار',
    'workflows' => 'گردش‌های کار',
    'title' => 'عنوان',
    'description' => 'توضیحات',
    'created' => 'گردش کار با موفقیت ایجاد شد.',
    'updated' => 'گردش کار با موفقیت به‌روزرسانی شد.',
    'deleted' => 'گردش کار با موفقیت حذف شد',
    'when' => 'وقتی',
    'then' => 'آنگاه',
    'field_change_to' => 'به',
    'total_executions' => 'اجرا: :total',
    'info' => 'ابزار گردش کار فرایند فروش را خودکار می‌کند؛ از جمله ایجاد فعالیت، ارسال ایمیل، درخواست HTTP و غیره.',

    'validation' => [
        'invalid_webhook_url' => 'نشانی webhook نباید با "https://" یا "http://" شروع شود',
    ],

    'actions' => [
        'webhook' => 'اجرای Webhook',
        'webhook_url_info' => 'باید یک URL کامل، معتبر و در دسترس عمومی باشد.',
    ],

    'fields' => [
        'with_header_name' => 'با نام هدر (اختیاری)',
        'with_header_value' => 'با مقدار هدر (اختیاری)',
        'for_owner' => 'برای: مالک (مسئول)',

        'dates' => [
            'now' => 'با تاریخ سررسید: الان',
            'in_1_day' => 'با تاریخ سررسید: یک روز دیگر',
            'in_2_days' => 'با تاریخ سررسید: دو روز دیگر',
            'in_3_days' => 'با تاریخ سررسید: سه روز دیگر',
            'in_4_days' => 'با تاریخ سررسید: چهار روز دیگر',
            'in_5_days' => 'با تاریخ سررسید: پنج روز دیگر',
            'in_1_week' => 'با تاریخ سررسید: ۱ هفته دیگر',
            'in_2_weeks' => 'با تاریخ سررسید: ۲ هفته دیگر',
            'in_1_month' => 'با تاریخ سررسید: ۱ ماه دیگر',
        ],
    ],
];
