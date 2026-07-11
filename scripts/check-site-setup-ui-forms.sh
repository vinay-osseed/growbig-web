#!/usr/bin/env bash

set -euo pipefail

echo "Checking site setup UI forms and services..."

ddev drush php:eval "\Drupal::service('site_platform_api.site_setup_profile_subscriber'); echo 'Site setup profile subscriber service OK' . PHP_EOL;"

ddev drush php:eval "\Drupal::formBuilder()->getForm('Drupal\site_platform_admin\Form\SiteSetupWizardForm'); echo 'Setup wizard form OK' . PHP_EOL;"
ddev drush php:eval "\Drupal::formBuilder()->getForm('Drupal\site_platform_admin\Form\SiteSetupRunForm'); echo 'Setup run form OK' . PHP_EOL;"
ddev drush php:eval "\Drupal::formBuilder()->getForm('Drupal\site_platform_admin\Form\SiteSetupCompleteForm'); echo 'Setup complete form OK' . PHP_EOL;"
ddev drush php:eval "\Drupal::formBuilder()->getForm('Drupal\site_platform_admin\Form\SiteSetupUnlockForm'); echo 'Setup unlock form OK' . PHP_EOL;"
ddev drush php:eval "\Drupal::formBuilder()->getForm('Drupal\site_platform_admin\Form\SiteSetupResetStatusForm'); echo 'Setup reset status form OK' . PHP_EOL;"

echo "Site setup UI forms and services verified."
