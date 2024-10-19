<?php
return [
    // Plesk API Configuration
    'plesk' => [
        'host' => 'https://dev.techneketax.com:8443', // Replace with your Plesk server URL
        'username' => 'admin', // Plesk admin username
        'password' => 'Team@@11@@', // Plesk admin password
        'api_endpoint' => '/enterprise/control/agent.php', // Default Plesk API endpoint
    ],
    // SMTP Configuration
    'smtp' => [
        'host' => 'techneke.com', // Your SMTP server
        'username' => @$_SESSION['users']['generated_email'].'@techneke.com', // SMTP username
        'password' => @$_SESSION['users']['generated_email_pass'], // SMTP password
        'port' => 587,
        'encryption' => 'tls',
    ],
    // IMAP Configuration
    'imap' => [
        'host' => 'techneke.com', // Your IMAP server
        'username' => @$_SESSION['users']['generated_email'].'@techneke.com', // SMTP username
        'password' => @$_SESSION['users']['generated_email_pass'],
        'port' => 993, // try 993, 995, 143
        'encryption' => 'ssl', // Encryption type: 'ssl' or 'tls'
    ],
    // Domain Configuration
    'domain' => 'techneketax.com', // The domain to create email addresses under
];

