<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration Hostinger
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique pour le déploiement sur Hostinger
    |
    */

    'domain' => 'jd-renovation-service.fr',
    'ssl' => true,
    'force_https' => true,
    
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'u182601382_jdrenov',
        'username' => 'u182601382_jdrenov',
        'password' => 'Harajuku1993@',
    ],
    
    'email' => [
        'host' => 'smtp.hostinger.com',
        'port' => 587,
        'encryption' => 'tls',
        'from_address' => 'noreply@jd-renovation-service.fr',
        'from_name' => 'JD Renovation Service',
    ],
    
    'paths' => [
        'public' => 'public/',
        'storage' => 'storage/',
        'bootstrap' => 'bootstrap/cache/',
    ],
    
    'permissions' => [
        'storage' => 0755,
        'bootstrap_cache' => 0755,
        'logs' => 0644,
    ],
];
