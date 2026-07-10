#!/usr/bin/env bash

set -euo pipefail

echo "Checking dashboard API..."

ddev drush php:eval '
use Drupal\user\Entity\User;

$account = User::load(1);
if (!$account) {
  throw new \RuntimeException("Could not load user 1.");
}

\Drupal::service("account_switcher")->switchTo($account);

try {
  $controller = \Drupal::classResolver()
    ->getInstanceFromDefinition(\Drupal\site_platform_api\Controller\AdminDashboardController::class);

  $response = $controller->dashboard();
  $data = json_decode($response->getContent(), TRUE);

  if (!is_array($data)) {
    throw new \RuntimeException("Dashboard response is not valid JSON.");
  }

  foreach (["id", "title", "roles", "cards", "counts", "recent"] as $key) {
    if (!array_key_exists($key, $data)) {
      throw new \RuntimeException("Missing dashboard key: {$key}");
    }
  }

  if ($data["id"] !== "admin_dashboard") {
    throw new \RuntimeException("Unexpected dashboard id.");
  }

  if (empty($data["cards"])) {
    throw new \RuntimeException("Dashboard cards should not be empty for admin user.");
  }

  foreach (["pages", "services", "partners", "teamMembers", "jobs", "contactSubmissions", "jobApplications"] as $count_key) {
    if (!array_key_exists($count_key, $data["counts"])) {
      throw new \RuntimeException("Missing dashboard count: {$count_key}");
    }
  }

  echo "Dashboard API verified." . PHP_EOL;
}
finally {
  \Drupal::service("account_switcher")->switchBack();
}
'
