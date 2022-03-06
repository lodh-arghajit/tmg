<?php

global $content_directories;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
$settings['config_sync_directory'] = dirname(DRUPAL_ROOT) . '/config';

/**
 * "Trusted host settings" are not necessary on Pantheon; traffic will only
 * be routed to your site if the host settings match a domain configured for
 * your site in the dashboard.
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
    $settings['trusted_host_patterns'][] = '.*';
}
$databases['default']['default'] = array (
  'database' => 'lamp',
  'username' => 'lamp',
  'password' => 'lamp',
  'prefix' => '',
  'host' => 'database',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
$settings['hash_salt'] = 'kO6yJeUCJTY8JK1HtGrFq142ORT659dJHhSLo9YAyCR0FmVhW-NOPIygVfuiIBw0xLtQ5ISRsg';
