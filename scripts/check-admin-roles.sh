#!/usr/bin/env bash

set -euo pipefail

echo "Checking admin roles..."

ddev drush php:eval '
$required = [
  "site_developer" => "Site Developer",
  "content_admin" => "Content Admin",
  "hr_manager" => "HR Manager",
  "form_manager" => "Form Manager",
  "analytics_viewer" => "Analytics Viewer",
];

foreach ($required as $id => $label) {
  $role = \Drupal\user\Entity\Role::load($id);

  if (!$role) {
    throw new \RuntimeException("Missing role: {$id}");
  }

  if ($role->label() !== $label) {
    throw new \RuntimeException("Unexpected role label for {$id}: " . $role->label());
  }

  echo $id . ": " . $role->label() . PHP_EOL;
}

echo "Admin roles verified." . PHP_EOL;
'
