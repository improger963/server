<?php

require_once 'vendor/autoload.php';

// Run the unit tests for notifications
echo "Running notification unit tests...\n";
system('php artisan test --filter=NotificationsTest');

echo "\nRunning notification feature tests...\n";
system('php artisan test --filter=NotificationSystemTest');