#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

PACKAGES=(
  drupal/domain
  drupal/layout_paragraphs
  drupal/pathauto
  drupal/metatag
  drupal/redirect
  drupal/simple_sitemap
  drupal/tour
)

MODULES=(
  domain
  domain_access
  layout_paragraphs
  pathauto
  metatag
  redirect
  simple_sitemap
  tour
  jsonapi
  menu_ui
  menu_link_content
  media
  media_library
  webform
  paragraphs
  entity_reference_revisions
  site_platform_native
  domain_alias
  domain_config
  domain_config_ui
  domain_content
  domain_source
  metatag_favicons
  redirect_domain
  rest
  basic_auth
  webform_node
  webform_ui
  webform_access
  webform_schema
  webform_image_select
  webform_options_custom
  webform_submission_export_import
  webform_submission_log
  datetime_range
  telephone
  toolbar
  admin_toolbar
  admin_toolbar_tools
  config_ignore
  config_split
  decoupled_router
  honeypot
  language
)

echo "Requiring contrib packages with Composer..."
ddev exec bash -lc 'cd /var/www/html/site-platform && composer require drupal/domain drupal/layout_paragraphs drupal/pathauto drupal/metatag drupal/redirect drupal/simple_sitemap drupal/tour --with-all-dependencies'

echo "Enabling Drupal-native modules..."
ddev drush en -y "${MODULES[@]}"
