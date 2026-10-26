<?php

return [
    'base_path' => env('DOCS_BASE_PATH', ''),
    'services' => [
        'master-service' => [
            'name' => 'Master Service',
            'description' => 'Master data management',
        ],
        'user-service' => [
            'name' => 'User Service',
            'description' => 'User & authentication management',
        ],
        'content-service' => [
            'name' => 'Content Service',
            'description' => 'Content management',
        ],
        'public-content-service' => [
            'name' => 'Public Content Service',
            'description' => 'Public content delivery',
        ],
        'file-service' => [
            'name' => 'File Service',
            'description' => 'File management',
        ],
        'setting-service' => [
            'name' => 'Setting Service',
            'description' => 'System settings',
        ],
        'project-service' => [
            'name' => 'Project Service',
            'description' => 'Project management',
        ]
    ],
];
