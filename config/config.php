<?php
/**
 * IndustrialCRM Configuration File
 * Contains general application and company settings.
 */

// Application Info
define('APP_NAME', 'Industrial CRM');
define('APP_VERSION', '2.0.0');

// Company Details (Used on Print Quotation & Invoice)
define('COMPANY_NAME', 'Accurate Hightensile Enterprise');
define('COMPANY_TAGLINE', 'Industrial Fasteners & Components');
define('COMPANY_ADDRESS', 'Ahmedabad, Gujarat, India');
define('COMPANY_PHONE', '+91-XXXXXXXXXX');
define('COMPANY_EMAIL', 'info@company.com');
define('CURRENCY_SYMBOL', '₹');

// Start secure session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    
    session_start();
}
