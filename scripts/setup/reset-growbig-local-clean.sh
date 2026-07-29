#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../.."

echo "This will DROP the local DDEV DB and delete local uploaded files."
echo "Only run this on local DDEV."
echo

read -r -p "Type RESET-GROWBIG-LOCAL to continue: " CONFIRM

if [ "$CONFIRM" != "RESET-GROWBIG-LOCAL" ]; then
  echo "Cancelled."
  exit 1
fi

echo "Dropping local DB..."
ddev drush sql:drop -y || true

echo "Cleaning local uploaded files..."
rm -rf site-platform/web/sites/default/files/*
rm -rf site-platform/private/*
mkdir -p site-platform/web/sites/default/files
mkdir -p site-platform/private

touch site-platform/web/sites/default/files/.gitkeep
touch site-platform/private/.gitkeep

echo "Installing fresh Drupal..."
ddev drush site:install standard \
  --account-name=admin \
  --account-pass=admin \
  --site-name="GrowBig Technologies LLP" \
  --site-mail="office@growbigllp.com" \
  -y

echo "Installing required modules..."
ddev drush en -y \
  file \
  image \
  link \
  options \
  telephone \
  datetime \
  datetime_range \
  toolbar \
  media \
  media_library \
  menu_ui \
  menu_link_content \
  path \
  path_alias \
  webform \
  webform_ui \
  metatag \
  redirect \
  simple_sitemap \
  jsonapi \
  rest \
  basic_auth \
  honeypot \
  admin_toolbar \
  admin_toolbar_tools \
  config_ignore \
  config_split \
  site_platform_api

echo "Running single-backend closeout..."
ddev drush scr scripts/setup/site_platform_local_single_backend_closeout.php

echo "Seeding frontend content..."
ddev drush scr scripts/setup/site_platform_seed_frontend_content.php

echo "Importing frontend media into Drupal Media..."
ddev drush scr scripts/setup/site_platform_import_frontend_media.php

echo "Re-seeding frontend payload with Drupal Media URLs..."
ddev drush scr scripts/setup/site_platform_seed_frontend_content.php

echo "Rebuilding caches..."
ddev drush cr

echo "Fresh GrowBig local backend reset completed."
