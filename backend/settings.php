<?php

return [
    'database' => [
        'dsn' => 'mysql:host=127.0.0.1:8111;dbname=email_sender',
        'username' => 'marie',
        'password' => '1410',
    ],
    'mail_settings_prod' => [
        'host' => 'smtp.gmail.com',
        'auth' => true,
        'port' => 465,
        'secure' => 'ssl',
        'username' => 'email@gmail.com',
        'password' => 'password',
        'charset' => 'UTF-8',
        'from_email' => 'email@gmail.com',
        'from_name' => 'Marie',
        'is_html' => true,
    ],
];
