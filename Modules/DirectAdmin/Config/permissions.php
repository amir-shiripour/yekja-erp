<?php

return [
    'groups' => [
        'directadmin' => [
            'label' => 'دایرکت ادمین (DirectAdmin)',
            'permissions' => [
                'directadmin.view' => 'مشاهده داشبورد دایرکت ادمین',
                'directadmin.settings.manage' => 'مدیریت تنظیمات اتصال سرور',
                'directadmin.accounts.view' => 'مشاهده اکانت‌های هاستینگ',
                'directadmin.accounts.create' => 'ساخت اکانت جدید',
                'directadmin.accounts.delete' => 'حذف اکانت هاستینگ',
                'directadmin.accounts.suspend' => 'تعلیق / رفع تعلیق اکانت',
                'directadmin.accounts.password' => 'تغییر رمز اکانت هاستینگ',
                'directadmin.accounts.sync' => 'همگام‌سازی اکانت‌ها با سرور',
                'directadmin.wordpress.manage' => 'مدیریت وردپرس در هاست',
                'directadmin.login-history.view' => 'مشاهده تاریخچه ورود کاربران',
            ],
        ],
    ],
];
