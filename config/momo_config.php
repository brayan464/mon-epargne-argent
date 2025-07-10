<?php
// Configuration de l’API MoMo (remplace les valeurs par les tiennes depuis https://momodeveloper.mtn.com)

define('MOMO_ENV', 'sandbox'); // ou 'production'
define('MOMO_API_USER_ID', 'remplace-par-user-id');
define('MOMO_API_KEY', 'remplace-par-api-key');
define('MOMO_PRIMARY_KEY', 'remplace-par-primary-key');
define('MOMO_TARGET_ENV', 'sandbox');
define('MOMO_CALLBACK_URL', 'https://ton-site.com/mtn_callback.php'); // doit être accessible depuis internet

define('MOMO_BASE_URL', MOMO_ENV === 'sandbox' 
    ? 'https://sandbox.momodeveloper.mtn.com' 
    : 'https://proxy.momoapi.mtn.com');
?>

