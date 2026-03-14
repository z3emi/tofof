<?php

return [
    'groups' => [
        'dashboard' => [
            'label' => 'لوحة التحكم',
            'permissions' => [
                'view-admin-panel'   => 'الدخول إلى لوحة التحكم',
                'view-activity-log'  => 'عرض سجل الأنشطة',
            ],
        ],

        'settings' => [
            'label' => 'إعدادات النظام',
            'permissions' => [
                'view-settings'   => 'عرض الإعدادات العامة',
                'edit-settings'   => 'تعديل الإعدادات العامة',
                'manage-backups'  => 'إدارة النسخ الاحتياطية',
                'manage-imports'  => 'إدارة الاستيراد الجماعي',
            ],
        ],

        'utilities' => [
            'label' => 'أدوات مساعدة',
            'permissions' => [
                'export-excel' => 'تصدير البيانات إلى Excel',
            ],
        ],

        'catalog' => [
            'label' => 'إدارة المنتجات',
            'permissions' => [
                'view-products'   => 'عرض المنتجات',
                'create-products' => 'إنشاء منتجات',
                'edit-products'   => 'تعديل المنتجات',
                'delete-products' => 'حذف المنتجات',

                'view-categories'   => 'عرض التصنيفات (البراند)',
                'create-categories' => 'إنشاء تصنيفات',
                'edit-categories'   => 'تعديل التصنيفات',
                'delete-categories' => 'حذف التصنيفات',

                'view-primary-categories'   => 'عرض الفئات الرئيسية',
                'create-primary-categories' => 'إنشاء فئات رئيسية',
                'edit-primary-categories'   => 'تعديل الفئات الرئيسية',
                'delete-primary-categories' => 'حذف الفئات الرئيسية',

                'view-barcodes'   => 'عرض أكواد الباركود',
                'create-barcodes' => 'إنشاء أكواد باركود',
                'edit-barcodes'   => 'تعديل أكواد الباركود',
                'delete-barcodes' => 'حذف أكواد الباركود',
            ],
        ],

        'orders' => [
            'label' => 'المبيعات',
            'permissions' => [
                'view-orders'          => 'عرض الطلبات',
                'view-team-orders'     => 'عرض طلبات الفريق المباشر',
                'view-all-orders'      => 'عرض جميع طلبات النظام',
                'view-credit-orders'   => 'عرض الطلبات الآجلة',
                'view-partial-delivery-orders' => 'عرض طلبات السداد الجزئي',
                'view-quotation-orders'=> 'عرض عروض الأسعار',
                'create-orders'        => 'إنشاء الطلبات',
                'edit-orders'          => 'تعديل الطلبات',
                'delete-orders'        => 'حذف الطلبات',
                'view-trashed-orders'  => 'عرض الطلبات المحذوفة',
                'restore-orders'       => 'استعادة الطلبات',
                'force-delete-orders'  => 'الحذف النهائي للطلبات',
                'update-order-status'  => 'تحديث حالة الطلب',
                'manage-order-discounts' => 'إدارة خصومات الطلبات',
                'apply-discount-codes'   => 'تطبيق أكواد الخصم على الطلبات',
                'view-order-invoices'  => 'عرض وطباعة فواتير الطلبات',
                'override-order-shipping-address' => 'تعديل عنوان التوصيل للطلبات اليدوية',
                'view-order-cost-field' => 'عرض حقل الكلفة في الطلبات اليدوية',
                'edit-order-responsible-manager' => 'تعديل البائع أو المدير المسؤول عن الطلب',
                'manage-order-sale-type'   => 'التحكم في نوع البيع للطلبات',
                'manage-order-invoice-type'=> 'التحكم في نوع الفاتورة للطلبات',
                'edit-order-item-prices'   => 'تعديل أسعار المنتجات عند إنشاء الطلب',
                'manage-order-payment-status' => 'تعديل حالة الدفع للطلبات',
            ],
        ],

        'tasks' => [
            'label' => 'إدارة المهام',
            'permissions' => [
                'view-own-tasks'      => 'عرض المهام المسندة إليه فقط',
                'view-team-tasks'     => 'عرض مهام الفريق المباشر',
                'view-all-tasks'      => 'عرض جميع المهام في النظام',
                'view-task-details'   => 'عرض تفاصيل المهام',
                'create-tasks'        => 'إنشاء مهام جديدة',
                'edit-tasks'          => 'تعديل المهام',
                'delete-tasks'        => 'حذف المهام',
                'assign-tasks'        => 'إسناد المهام لمدراء آخرين',
                'manage-task-status'  => 'تغيير حالة المهام (عبر السحب)',
                'edit-task-creator'   => 'تعديل منشئ المهمة (للإدارة العليا)',
                'comment-on-tasks'    => 'إضافة تعليقات على المهام',
                'reopen-completed-tasks' => 'إعادة فتح المهام المكتملة',
            ],
        ],

        'customers' => [
            'label' => 'العملاء والمبيعات',
            'permissions' => [
                'view-customers'   => 'عرض العملاء',
                'view-website-customers' => 'عرض عملاء الموقع',
                'create-customers' => 'إنشاء عملاء',
                'edit-customers'   => 'تعديل العملاء',
                'edit-customer-addresses' => 'تعديل عناوين العملاء',
                'delete-customers' => 'حذف العملاء',
                'view-trashed-customers' => 'عرض العملاء المحذوفين',
                'restore-customers' => 'استعادة العملاء',
                'force-delete-customers' => 'الحذف النهائي للعملاء',
                'ban-customers'    => 'حظر العملاء',
                'manage-wallet'    => 'إدارة محفظة العميل',
                'view-all-customer-zones'      => 'عرض جميع العملاء في كل المحافظات',
                'view-customer-governorate-stats' => 'عرض إحصائيات العملاء حسب المحافظة',

            ],
        ],

        'suppliers' => [
            'label' => 'الموردون والمشتريات',
            'permissions' => [
                'view-suppliers'   => 'عرض الموردين',
                'create-suppliers' => 'إنشاء موردين',
                'edit-suppliers'   => 'تعديل الموردين',
                'delete-suppliers' => 'حذف الموردين',

                'view-purchases'   => 'عرض فواتير الشراء',
                'create-purchases' => 'إنشاء فواتير شراء',
                'edit-purchases'   => 'تعديل فواتير الشراء',
                'delete-purchases' => 'حذف فواتير الشراء',
            ],
        ],

        'wallet' => [
            'label' => 'محفظة العملاء والصندوق',
            'permissions' => [
                'view-cash-boxes'   => 'عرض الصناديق النقدية',
                'create-cash-boxes' => 'إضافة صندوق نقدي',

                'view-any-receipt-voucher' => 'عرض جميع سندات القبض',
                'view-own-receipt-voucher' => 'عرض سندات القبض الخاصة بالموظف',
                'create-receipt-voucher'   => 'إنشاء سند قبض',
                'edit-receipt-voucher'     => 'تعديل سند قبض',
                'approve-receipt-voucher'  => 'اعتماد سند قبض',
                'delete-receipt-voucher'   => 'حذف سند قبض',

                'view-deposit-vouchers'  => 'عرض سندات الإيداع',
                'create-deposit-voucher' => 'تسجيل إيداع من المندوب',
                'edit-deposit-voucher'   => 'تعديل سند إيداع',
                'delete-deposit-voucher' => 'حذف سند إيداع',

                'view-any-payment-voucher' => 'عرض جميع سندات الصرف',
                'view-own-payment-voucher' => 'عرض سندات الصرف الخاصة به',
                'create-payment-voucher'   => 'إنشاء سند صرف',
                'approve-payment-voucher'  => 'اعتماد سند صرف',
                'delete-payment-voucher'   => 'حذف سند صرف',

                'view-any-internal-transfer' => 'عرض جميع التحويلات الداخلية',
                'view-own-internal-transfer' => 'عرض التحويلات الداخلية الخاصة به',
                'create-internal-transfer'   => 'إنشاء تحويل داخلي',
                'approve-internal-transfer'  => 'اعتماد تحويل داخلي',
            ],
        ],

        'accounting' => [
            'label' => 'نظام الحسابات',
            'permissions' => [
                'access-accounting'                 => 'الوصول إلى نظام الحسابات',
                'view-accounting-accounts'          => 'عرض دليل الحسابات',
                'view-accounting-invoices'          => 'عرض فواتير الحسابات',
                'create-accounting-invoices'        => 'إنشاء فواتير الحسابات',
                'assign-accounting-invoice-manager' => 'تعيين مسؤول فاتورة الحسابات',
                'record-accounting-invoice-payments'=> 'تسجيل دفعات الفواتير المحاسبية',

                'view-accounting-cash-accounts'   => 'عرض حسابات الصندوق المحاسبية',
                'create-accounting-cash-accounts' => 'إضافة حساب صندوق محاسبي',
                'edit-accounting-cash-accounts'   => 'تعديل حسابات الصندوق المحاسبية',
                'delete-accounting-cash-accounts' => 'حذف حسابات الصندوق المحاسبية',

                'view-accounting-journal-entries'   => 'عرض قيود اليومية',
                'create-accounting-journal-entries' => 'إضافة قيود اليومية',

                'view-any-allowance-voucher' => 'عرض سندات السماح',
                'view-own-allowance-voucher' => 'عرض سندات السماح الخاصة به',
                'create-allowance-voucher'   => 'إنشاء سند سماح',

                'view-accounting-reports'            => 'عرض لوحة تقارير الحسابات',
                'view-accounting-report-trial-balance'=> 'عرض ميزان المراجعة',
                'view-accounting-report-income'       => 'عرض تقرير الأرباح والخسائر',
                'view-accounting-report-ledger'       => 'عرض كشف حساب عام',
                'view-accounting-report-sales'        => 'عرض تقرير مبيعات العملاء',
                'view-accounting-report-aging'        => 'عرض تقرير أعمار الديون',
                'view-accounting-report-customer'     => 'عرض كشف حساب العميل',
                'view-accounting-report-manager'      => 'عرض تقرير نشاط المسؤولين',
                'view-customer-collection-report'     => 'عرض تقرير تحصيل العملاء',
                'view-representative-delivery-report' => 'عرض تقرير تسليم المندوبين',
                'view-collector-balances-report'      => 'عرض تقرير عهد المندوبين',
                'view-cash-account-statement'         => 'عرض كشف حركة الصندوق',
                'view-customer-wallet-report'         => 'عرض تقارير محفظة العملاء',
            ],
        ],

        'finance' => [
            'label' => 'الشؤون المالية والتقارير',
            'permissions' => [
                'view-expenses'            => 'عرض المصاريف',
                'create-expenses'          => 'إضافة مصاريف',
                'edit-expenses'            => 'تعديل المصاريف',
                'delete-expenses'          => 'حذف المصاريف',
                'view-inventory'           => 'عرض المخزون',
                'manage-inventory'         => 'تعديل كميات المخزون',
                'view-reports'             => 'عرض لوحة التقارير',
                'view-reports-financial'   => 'عرض تقارير المبيعات',
                'view-reports-inventory'   => 'عرض تقارير المخزون',
                'view-reports-customers'   => 'عرض تقارير العملاء',
            ],
        ],

        'marketing' => [
            'label' => 'التسويق',
            'scope' => 'website',
            'permissions' => [
                'view-discount-codes'   => 'عرض أكواد الخصم',
                'create-discount-codes' => 'إنشاء أكواد خصم',
                'edit-discount-codes'   => 'تعديل أكواد الخصم',
                'delete-discount-codes' => 'حذف أكواد الخصم',

                'view-blog'   => 'عرض المدونة',
                'create-blog' => 'إنشاء مقالات',
                'edit-blog'   => 'تعديل المقالات',
                'delete-blog' => 'حذف المقالات',
            ],
        ],

        'website' => [
            'label' => 'إدارة الموقع',
            'scope' => 'website',
            'permissions' => [
                'access-website-module' => 'الوصول إلى قسم الموقع',
            ],
        ],

        'users' => [
            'label' => 'المستخدمون',
            'permissions' => [
                'view-users'           => 'عرض المستخدمين',
                'create-users'         => 'إنشاء مستخدمين',
                'edit-users'           => 'تعديل المستخدمين',
                'delete-users'         => 'حذف المستخدمين',
                'view-trashed-users'   => 'عرض المستخدمين المحذوفين',
                'restore-users'        => 'استعادة المستخدمين',
                'force-delete-users'   => 'الحذف النهائي للمستخدمين',
                'ban-users'            => 'حظر المستخدمين',
                'logout-users'         => 'تسجيل خروج المستخدمين',
                'impersonate-users'    => 'انتحال المستخدمين',
                'view-pending-users'   => 'عرض المستخدمين قيد التفعيل',
            ],
        ],

        'managers' => [
            'label' => 'المدراء والفريق',
            'permissions' => [
                'view-managers'         => 'عرض المدراء (الفريق المباشر)',
                'view-managers-all'     => 'عرض جميع المدراء',
                'view-manager-contact'  => 'عرض بيانات التواصل للمدراء',
                'create-managers'       => 'إضافة المدراء',
                'edit-managers'         => 'تعديل المدراء',
                'edit-manager-name'     => 'تعديل اسم المدير',
                'edit-manager-email'    => 'تعديل البريد الإلكتروني للمدير',
                'edit-manager-phone'    => 'تعديل رقم هاتف المدير',
                'edit-manager-password' => 'تعديل كلمة مرور المدير',
                'edit-manager-status'   => 'تعديل حالة التفعيل للمدير',
                'delete-managers'       => 'حذف المدراء',
                'view-trashed-managers' => 'عرض المدراء المحذوفين',
                'restore-managers'      => 'استعادة المدراء',
                'force-delete-managers' => 'الحذف النهائي للمدراء',
                'ban-managers'          => 'حظر المدراء',
                'logout-managers'       => 'تسجيل خروج المدراء',
                'impersonate-managers'  => 'انتحال المدراء',
            ],
        ],

        'roles' => [
            'label' => 'الأدوار',
            'permissions' => [
                'view-roles'   => 'عرض الأدوار',
                'create-roles' => 'إنشاء الأدوار',
                'edit-roles'   => 'تعديل الأدوار',
                'delete-roles' => 'حذف الأدوار',
            ],
        ],

        'reviews' => [
            'label' => 'المراجعات والتقييمات',
            'permissions' => [
                'manage-reviews' => 'إدارة تقييمات المنتجات',
            ],
        ],

        'tiers' => [
            'label' => 'فئات العملاء',
            'permissions' => [
                'manage-customer-tiers' => 'إدارة فئات الولاء',
            ],
        ],

        'hr' => [
            'label' => 'الموارد البشرية',
            'permissions' => [
                'manage_employee_profiles'  => 'إدارة ملفات الموظفين',
                'view_payroll'              => 'مشاهدة مسير الرواتب',
                'process_payroll'           => 'تنفيذ عملية مسير الرواتب',
                'revert_payroll'            => 'التراجع عن مسير الرواتب',
                'approve_leave_requests'    => 'الموافقة على طلبات الإجازة',
                'approve_advance_requests'  => 'الموافقة على طلبات السلف',
                'view_own_payslip'          => 'مشاهدة قسيمة الراتب الخاصة بالموظف',
                'sales-rep'                 => 'تفويض الموظف كمندوب تحصيل',
            ],
        ],
    ],

    'roles' => [
        'Super-Admin' => ['*'],
        'Order-Manager' => [
            'view-admin-panel',
            'view-orders',
            'view-team-orders',
            'view-credit-orders',
            'view-partial-delivery-orders',
            'view-quotation-orders',
            'create-orders',
            'edit-orders',
            'update-order-status',
            'manage-order-discounts',
            'apply-discount-codes',
            'override-order-shipping-address',
            'view-order-cost-field',
            'edit-order-responsible-manager',
            'manage-order-sale-type',
            'manage-order-invoice-type',
            'edit-order-item-prices',
            'view-order-invoices',
            'view-customers',
            'view-reports',
            'view-reports-financial',
            'view-inventory',
            'manage-inventory',
        ],
        'Content-Creator' => [
            'view-admin-panel',
            'access-website-module',
            'view-products',
            'create-products',
            'edit-products',
            'view-categories',
            'create-categories',
            'edit-categories',
            'view-blog',
            'create-blog',
            'edit-blog',
        ],
        'HR-Manager' => [
            'view-admin-panel',
            'manage_employee_profiles',
            'view_payroll',
            'process_payroll',
            'revert_payroll',
            'approve_leave_requests',
            'approve_advance_requests',
        ],
        'user' => [
            'view_own_payslip',
        ],
    ],
];
