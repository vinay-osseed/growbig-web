#!/usr/bin/env bash
set -euo pipefail

ddev drush php:eval '
$checks = [
  "contact_us" => "office@growbigllp.com",
  "job_application" => "office@growbigllp.com",
];

foreach ($checks as $webform_id => $expected_to) {
  $webform = \Drupal\webform\Entity\Webform::load($webform_id);

  if (!$webform) {
    throw new \RuntimeException("Missing webform: {$webform_id}");
  }

  $handlers = $webform->get("handlers") ?: [];
  $found = FALSE;

  foreach ($handlers as $handler) {
    if (
      ($handler["id"] ?? "") === "email"
      && !empty($handler["status"])
      && (($handler["settings"]["to_mail"] ?? "") === $expected_to)
    ) {
      $found = TRUE;
      break;
    }
  }

  if (!$found) {
    throw new \RuntimeException("Missing enabled email handler for {$webform_id} to {$expected_to}");
  }

  echo "Verified email handler: {$webform_id} -> {$expected_to}" . PHP_EOL;
}
'
