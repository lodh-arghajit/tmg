<?php

// Prepare a LANDO_INFO constant.
define('LANDO_INFO', json_decode($_ENV['LANDO_INFO'], TRUE));
if (file_exists(dirname(DRUPAL_ROOT) . '/load.environment.php')) {
    include dirname(DRUPAL_ROOT) . '/load.environment.php';
}
$settings['hash_salt'] = 'kO6yJeUCJTY8JK1HtGrFq142ORT659dJHhSLo9YAyCR0FmVhW-NOPIygVfuiIBw0xLtQ5ISRsg';
// When using lando, use Lando settings.
if (defined('LANDO_INFO') && !empty(LANDO_INFO['database']['creds']['database'])) {
    // Databases.
  $databases['default']['default'] = [
        // Since "mariadb" drivers are the same as "mysql", we hard-code "mysql".
        'driver' => 'mysql',
        'database' => LANDO_INFO['database']['creds']['database'],
        'username' => LANDO_INFO['database']['creds']['user'],
        'password' => LANDO_INFO['database']['creds']['password'],
        'host' => LANDO_INFO['database']['internal_connection']['host'],
        'port' => LANDO_INFO['database']['internal_connection']['port'],
        'prefix' => '',
        'collation' => 'utf8mb4_general_ci',
    ];
  $settings['trusted_host_patterns'][] = '^.*\.lndo\.site$';
}
elseif (!empty($_ENV['DB_NAME'])) {
  $databases['default']['default'] = [
    // Since "mariadb" drivers are the same as "mysql", we hard-code "mysql".
    'driver' => 'mysql',
    'database' => $_ENV['DB_NAME'],
    'username' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'],
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'prefix' => '',
    'collation' => 'utf8mb4_general_ci',
  ];
}
// When using xampp, use .env settings.
else {
  $databases['default']['default'] = array (
    'database' => 'lamp',
    'username' => 'root',
    'password' => '',
    'prefix' => '',
    'host' => 'localhost',
    'port' => '3306',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
  );

}
// Trusted host patterns.


