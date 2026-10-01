<?php
/**
 * IndustrialCRM Configuration File
 * Contains general application and company settings.
 */

// Application Info
if (!defined('APP_NAME'))        define('APP_NAME', 'Industrial CRM');
if (!defined('APP_VERSION'))     define('APP_VERSION', '2.0.0');

// Company Details (Used on Print Quotation & Invoice)
if (!defined('COMPANY_NAME'))    define('COMPANY_NAME', 'Accurate Hightensile Enterprise');
if (!defined('COMPANY_TAGLINE')) define('COMPANY_TAGLINE', 'Industrial Fasteners & Components');
if (!defined('COMPANY_ADDRESS')) define('COMPANY_ADDRESS', 'Ahmedabad, Gujarat, India');
if (!defined('COMPANY_PHONE'))   define('COMPANY_PHONE', '+91-XXXXXXXXXX');
if (!defined('COMPANY_EMAIL'))   define('COMPANY_EMAIL', 'info@company.com');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '₹');

// Start secure session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    
    session_start();
}
