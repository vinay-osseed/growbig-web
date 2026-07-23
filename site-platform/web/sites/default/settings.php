<?php

declare(strict_types=1);

/**
 * @file
 * Site Platform shared Drupal settings.
 *
 * This file is committed and must stay environment-neutral.
 * Do not add database credentials, salts, API keys, or host-specific values here.
 */

$databases = [];

// Load shared Site Platform defaults.
$site_platform_shared_settings = $app_root . '/' . $site_path . '/settings.platform.php';
if (file_exists($site_platform_shared_settings)) {
  include $site_platform_shared_settings;
}

// Load DDEV database/settings when running inside DDEV.
// DDEV may generate this file locally. It should not be edited manually.
$site_platform_ddev_settings = $app_root . '/' . $site_path . '/settings.ddev.php';
if (getenv('IS_DDEV_PROJECT') === 'true' && file_exists($site_platform_ddev_settings)) {
  include $site_platform_ddev_settings;
}

// Load optional production override file.
// The real settings.prod.php file is not committed.
$site_platform_prod_settings = $app_root . '/' . $site_path . '/settings.prod.php';
if (file_exists($site_platform_prod_settings)) {
  include $site_platform_prod_settings;
}

// Load optional developer local override file last.
// The real settings.local.php file is not committed.
$site_platform_local_settings = $app_root . '/' . $site_path . '/settings.local.php';
if (file_exists($site_platform_local_settings)) {
  include $site_platform_local_settings;
}
