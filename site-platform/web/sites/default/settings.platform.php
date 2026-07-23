<?php

declare(strict_types=1);

/**
 * @file
 * Shared Site Platform settings.
 *
 * This file is committed and should be safe for all environments.
 */

$site_platform_env = strtolower((string) (getenv('DRUPAL_ENV') ?: 'dev'));

$settings['config_sync_directory'] = '../config/sync';
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = '../private';
$settings['file_temp_path'] = '/tmp';

// Avoid filesystem scans through frontend dependency folders.
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

// Keep local/dev install simple. Production must provide DRUPAL_HASH_SALT.
if (empty($settings['hash_salt'])) {
  $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'site-platform-dev-only-change-in-prod';
}

// Environment-aware config split activation.
// Use DRUPAL_ENV=dev for DDEV/local/shared development.
// Use DRUPAL_ENV=prod for production/live.
// Stage can be added later without changing the pattern.
foreach (['dev', 'prod'] as $site_platform_split) {
  $config["config_split.config_split.$site_platform_split"]['status'] = ($site_platform_env === $site_platform_split);
}

// Build trusted host patterns from environment variables.
// Keep this environment-driven so the same codebase can serve many domains.
$site_platform_domains = [];

foreach ([
  'DRUPAL_PRIMARY_DOMAIN',
  'DRUPAL_ADMIN_DOMAIN',
  'DRUPAL_API_DOMAIN',
  'DRUPAL_UI_DOMAIN',
] as $env_name) {
  $value = trim((string) getenv($env_name));
  if ($value !== '') {
    $site_platform_domains[] = $value;
  }
}

$extra_hosts = trim((string) getenv('DRUPAL_EXTRA_TRUSTED_HOSTS'));
if ($extra_hosts !== '') {
  $site_platform_domains = array_merge(
    $site_platform_domains,
    array_map('trim', explode(',', $extra_hosts))
  );
}

if ($site_platform_domains !== []) {
  $settings['trusted_host_patterns'] = array_values(array_map(
    static fn(string $domain): string => '^' . preg_quote($domain, '/') . '$',
    array_unique(array_filter($site_platform_domains))
  ));
}

// Local/dev fallback for first install and onboarding.
if ($site_platform_domains === [] && $site_platform_env !== 'prod') {
  $settings['trusted_host_patterns'] = ['.*'];
}

// Production database from environment variables.
// This avoids committing a production settings file while still supporting Docker.
if ($site_platform_env === 'prod' && empty($databases['default']['default'])) {
  $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: $settings['hash_salt'];

  $databases['default']['default'] = [
    'database' => getenv('DRUPAL_DATABASE_NAME') ?: 'site_platform',
    'username' => getenv('DRUPAL_DATABASE_USER') ?: 'site_platform',
    'password' => getenv('DRUPAL_DATABASE_PASSWORD') ?: '',
    'host' => getenv('DRUPAL_DATABASE_HOST') ?: 'db',
    'port' => getenv('DRUPAL_DATABASE_PORT') ?: '3306',
    'driver' => 'mysql',
    'prefix' => '',
    'collation' => 'utf8mb4_general_ci',
  ];
}

// Production reverse proxy support.
if ($site_platform_env === 'prod') {
  $settings['reverse_proxy'] = TRUE;
  $settings['reverse_proxy_trusted_headers'] =
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR |
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST |
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO |
    \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT;

  $reverse_proxy_addresses = trim((string) getenv('DRUPAL_REVERSE_PROXY_ADDRESSES'));
  if ($reverse_proxy_addresses !== '') {
    $settings['reverse_proxy_addresses'] = array_map('trim', explode(',', $reverse_proxy_addresses));
  }
}

// Load production service overrides when available.
$site_platform_prod_services = $app_root . '/' . $site_path . '/services.prod.yml';
if ($site_platform_env === 'prod' && file_exists($site_platform_prod_services)) {
  $settings['container_yamls'][] = $site_platform_prod_services;
}
