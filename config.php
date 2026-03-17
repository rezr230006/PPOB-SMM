// path: config.php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kincaipayment');

// Pakasir Configuration
define('PAKASIR_API_KEY', 'your_pakasir_api_key');
define('PAKASIR_SECRET_KEY', 'your_pakasir_secret_key');
define('PAKASIR_MERCHANT_ID', 'your_pakasir_merchant_id');
define('PAKASIR_WEBHOOK_URL', 'https://yourdomain.com/action/callback-pakasir.php');

// ZaynFlazz Configuration
define('ZAYNFLAZZ_API_KEY', 'your_zaynflazz_api_key');
define('ZAYNFLAZZ_API_URL', 'https://api.zaynflazz.com');
define('ZAYNFLAZZ_WEBHOOK_URL', 'https://yourdomain.com/action/callback-zaynflazz.php');

// General Configuration
define('SITE_URL', 'https://yourdomain.com');
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('AUTO_SYNC_INTERVAL', 21600); // 6 hours in seconds
define('ORDER_CHECK_INTERVAL', 300); // 5 minutes in seconds