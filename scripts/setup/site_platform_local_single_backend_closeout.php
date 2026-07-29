<?php

declare(strict_types=1);

/**
 * Local single-backend closeout.
 *
 * This script finalizes the local backend as a single Drupal decoupled backend.
 * It intentionally avoids Domain/multisite requirements.
 */

$moduleHandler = \Drupal::moduleHandler();
$moduleInstaller = \Drupal::service('module_installer');
$configFactory = \Drupal::configFactory();

$requiredModules = [
  'file',
  'image',
  'link',
  'options',
  'telephone',
  'datetime_range',
  'language',
  'toolbar',
  'media',
  'media_library',
  'menu_ui',
  'menu_link_content',
  'paragraphs',
  'entity_reference_revisions',
  'layout_paragraphs',
  'webform',
  'webform_node',
  'webform_ui',
  'webform_access',
  'webform_schema',
  'webform_image_select',
  'webform_options_custom',
  'webform_submission_export_import',
  'webform_submission_log',
  'pathauto',
  'metatag',
  'metatag_favicons',
  'redirect',
  'simple_sitemap',
  'jsonapi',
  'rest',
  'basic_auth',
  'decoupled_router',
  'honeypot',
  'admin_toolbar',
  'admin_toolbar_tools',
  'config_ignore',
  'config_split',
];

$toInstall = [];

foreach ($requiredModules as $module) {
  if (!$moduleHandler->moduleExists($module)) {
    $toInstall[] = $module;
  }
}

if ($toInstall) {
  $moduleInstaller->install($toInstall, TRUE);
  print "Installed required single-backend modules:\n";
  foreach ($toInstall as $module) {
    print "- {$module}\n";
  }
}
else {
  print "Required single-backend modules already installed.\n";
}

$configFactory->getEditable('site_platform.runtime')
  ->set('mode', 'single_backend')
  ->set('frontend_count', 1)
  ->set('multisite_supported', FALSE)
  ->set('domain_required', FALSE)
  ->save(TRUE);

$configFactory->getEditable('system.site')
  ->set('name', 'Drupal Decoupled Backend Template')
  ->save(TRUE);

$domainModules = [
  'domain',
  'domain_access',
  'domain_alias',
  'domain_config',
  'domain_config_ui',
  'domain_content',
  'domain_source',
];

$installedDomainModules = [];

foreach ($domainModules as $module) {
  if ($moduleHandler->moduleExists($module)) {
    $installedDomainModules[] = $module;
  }
}

if ($installedDomainModules) {
  print "Domain modules are currently installed locally, but they are no longer required by the supported single-backend setup:\n";
  foreach ($installedDomainModules as $module) {
    print "- {$module}\n";
  }
  print "Leaving them installed for now to avoid unsafe config dependency breakage.\n";
}
else {
  print "Domain modules are not installed.\n";
}

drupal_flush_all_caches();

print "Local single-backend closeout completed.\n";
