<?php

/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'permissions' => 'دسترسی‌ها',
    'role' => 'نقش',
    'roles' => 'نقش‌ها',
    'name' => 'نام',
    'create' => 'ایجاد نقش',
    'edit' => 'ویرایش نقش',
    'created' => 'نقش با موفقیت ایجاد شد',
    'updated' => 'نقش با موفقیت به‌روزرسانی شد',
    'deleted' => 'نقش با موفقیت حذف شد',

    'granted' => 'اعطا شده',
    'revoked' => 'لغو شده',

    'capabilities' => [
        'access' => 'دسترسی',
        'view' => 'مشاهده',
        'delete' => 'حذف',
        'bulk_delete' => 'حذف گروهی',
        'edit' => 'ویرایش',
        'all' => 'همه :resourceName',
        'owning_only' => 'فقط مالک',
    ],

    'view_non_authorized_after_record_create' => 'به‌دلیل عدم مالکیت این سابقه، اجازه مشاهده آن را ندارید؛ پس از خروج از این صفحه دیگر به سابقه دسترسی نخواهید داشت.',

    'empty_state' => [
        'title' => 'بدون نقش',
        'description' => 'با ایجاد یک نقش جدید شروع کنید.',
    ],
];
