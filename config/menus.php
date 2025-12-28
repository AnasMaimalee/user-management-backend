<?php

return [
    'items' => [

        [
            'title' => 'Employees',
            'route' => '/employees',
            'icon' => 'UsergroupAddOutlined',
            'roles' => ['super_admin', 'hr'], // Only admin & HR
        ],
        [
            'title' => 'Departments',
            'route' => '/departments',
            'icon' => 'ApartmentOutlined',
            'roles' => ['super_admin', 'hr'],
        ],
        [
            'title' => 'Branches',
            'route' => '/branches',
            'icon' => 'BankOutlined',
            'roles' => ['super_admin', 'hr'],
        ],
        [
            'title' => 'Job Ranks',
            'route' => '/ranks',
            'icon' => 'ProfileOutlined',
            'roles' => ['super_admin', 'hr'],
        ],
        [
            'title' => 'Payroll',
            'route' => '/payroll',
            'icon' => 'DollarOutlined',
            'roles' => ['super_admin', 'hr'],
        ],
        [
            'title' => 'Loan Requests',
            'route' => '/admin/loans',
            'icon' => 'MoneyCollectOutlined',
            'roles' => ['super_admin', 'hr'],
        ],
        [
            'title' => 'My Wallet',
            'route' => '/wallet/my',
            'icon' => 'WalletOutlined',
            // No roles = visible to everyone
        ],
        [
            'title' => 'My Loans',
            'route' => '/loans/my',
            'icon' => 'DollarOutlined',
        ],
        [
            'title' => 'Profile',
            'route' => '/profile',
            'icon' => 'UserOutlined',
        ],
    ],
];
