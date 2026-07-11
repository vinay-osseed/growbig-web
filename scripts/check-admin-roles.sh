#!/usr/bin/env bash

set -euo pipefail

echo "Checking admin roles..."

ddev drush php:eval '
$role_storage = \Drupal::entityTypeManager()->getStorage("user_role");

$required = ["administrator", "content_editor", "hr_manager"];
foreach ($required as $rid) {
  if (!$role_storage->load($rid)) {
    throw new \RuntimeException("Missing required role: " . $rid);
  }
  echo "Role exists: $rid\n";
}

$not_allowed = ["site_developer", "content_admin", "form_manager", "analytics_viewer"];
foreach ($not_allowed as $rid) {
  if ($role_storage->load($rid)) {
    throw new \RuntimeException("Unwanted setup role still exists: " . $rid);
  }
  echo "Role removed or absent: $rid\n";
}
'

echo "Admin role check passed."
