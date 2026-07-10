#!/usr/bin/env bash

set -euo pipefail

echo "Checking required webforms..."

ddev drush php:eval '
$required = [
  "job_application" => "Job Application",
  "contact_us" => "Contact Us",
];

foreach ($required as $id => $label) {
  $webform = \Drupal\webform\Entity\Webform::load($id);

  if (!$webform) {
    throw new \RuntimeException("Missing webform: {$id}");
  }

  if ($webform->label() !== $label) {
    throw new \RuntimeException("Unexpected label for {$id}: " . $webform->label());
  }

  echo $id . ": " . $webform->label() . PHP_EOL;
}

$default_contact = \Drupal\webform\Entity\Webform::load("contact");
if ($default_contact) {
  throw new \RuntimeException("Default contact webform should not exist.");
}

echo "Required webforms verified." . PHP_EOL;
'
