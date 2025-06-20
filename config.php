<?php
// config.php — Global configuration settings for Hotel System

// ============================
// Timezone & Locale
// ============================
date_default_timezone_set('Europe/London'); // Adjust as needed
setlocale(LC_TIME, 'en_GB');

// ============================
// Error Reporting
// ============================
ini_set('display_errors', 1);         // Display errors in development
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);               // Log all types of errors

// To suppress errors in production:
// error_reporting(0);
// ini_set('display_errors', 0);

// ============================
// System Constants
// ============================
define('HOTEL_NAME', 'Hotel Deluxe');
define('BASE_URL', 'http://localhost/hotel-system'); // Update if deployed
define('ADMIN_EMAIL', 'admin@hoteldeluxe.com');

// ============================
// Session Settings
// ============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
