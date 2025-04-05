<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mail Servers
    |--------------------------------------------------------------------------
    |
    | This file contains the default configurations for various mail servers
    | that can be used in the application.
    |
    */

    'gmail' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.gmail.com',
        'mail_port' => 587,
        'mail_encryption' => 'tls',
        'description' => 'Gmail SMTP (TLS)',
        'alternative_ports' => [
            ['port' => 465, 'encryption' => 'ssl', 'description' => 'Gmail SMTP (SSL)']
        ]
    ],
    
    'yandex' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.yandex.ru',
        'mail_port' => 465,
        'mail_encryption' => 'ssl',
        'description' => 'Yandex SMTP (SSL)',
        'alternative_ports' => [
            ['port' => 587, 'encryption' => 'tls', 'description' => 'Yandex SMTP (TLS)']
        ]
    ],
    
    'mail_ru' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.mail.ru',
        'mail_port' => 465,
        'mail_encryption' => 'ssl',
        'description' => 'Mail.ru SMTP (SSL)',
        'alternative_ports' => [
            ['port' => 587, 'encryption' => 'tls', 'description' => 'Mail.ru SMTP (TLS)']
        ]
    ],
    
    'outlook' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.office365.com',
        'mail_port' => 587,
        'mail_encryption' => 'tls',
        'description' => 'Outlook/Office 365 SMTP (TLS)',
        'alternative_ports' => [
            ['port' => 465, 'encryption' => 'ssl', 'description' => 'Outlook/Office 365 SMTP (SSL)']
        ]
    ],
    
    'yahoo' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.mail.yahoo.com',
        'mail_port' => 465,
        'mail_encryption' => 'ssl',
        'description' => 'Yahoo SMTP (SSL)',
        'alternative_ports' => [
            ['port' => 587, 'encryption' => 'tls', 'description' => 'Yahoo SMTP (TLS)']
        ]
    ],
    
    'protonmail' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.protonmail.ch',
        'mail_port' => 587,
        'mail_encryption' => 'tls',
        'description' => 'ProtonMail SMTP (TLS)',
        'alternative_ports' => [
            ['port' => 465, 'encryption' => 'ssl', 'description' => 'ProtonMail SMTP (SSL)']
        ]
    ],
    
    'zoho' => [
        'mail_driver' => 'smtp',
        'mail_host' => 'smtp.zoho.com',
        'mail_port' => 587,
        'mail_encryption' => 'tls',
        'description' => 'Zoho SMTP (TLS)',
        'alternative_ports' => [
            ['port' => 465, 'encryption' => 'ssl', 'description' => 'Zoho SMTP (SSL)']
        ]
    ],
    
    'custom' => [
        'mail_driver' => 'smtp',
        'mail_host' => '',
        'mail_port' => 587,
        'mail_encryption' => 'tls',
        'description' => 'Пользовательский SMTP сервер',
        'alternative_ports' => [
            ['port' => 465, 'encryption' => 'ssl', 'description' => 'SSL порт']
        ]
    ]
]; 