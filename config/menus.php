<?php

return [
    [
        'title' => 'Departments',
        'permission' => 'view departments',
        'icon' => 'HomeOutlined',
        'route' => '/departments',
        'roles' => ['admin', 'hr'],
    ],
    [
        'title' => 'Employees',
        'permission' => 'view employees',
        'icon' => 'UserOutlined',
        'route' => '/employees',
        'roles' => ['admin', 'hr'],
    ],
    [
        'title' => 'Leaves',
        'permission' => 'view leaves',
        'icon' => 'UserOutlined',
        'route' => '/leaves',
        'roles' => ['admin', 'hr', 'staff'], // staff can also see leaves
    ],
    [
        'title' => 'Submit Leave',
        'permission' => 'create leaves',
        'icon' => 'PlusOutlined',
        'route' => '/leaves/submit',
        'roles' => ['staff'],
    ],
    [
        'title' => 'Settings',
        'permission' => 'view settings',
        'icon' => 'SettingOutlined',
        'route' => '/settings',
        'roles' => ['admin'],
    ],
];
