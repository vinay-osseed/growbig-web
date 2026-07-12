#!/usr/bin/env bash

set -euo pipefail

echo "Checking admin UX dashboard and editor defaults..."

ddev drush php:eval '
use Drupal\site_platform_api\Controller\AdminDashboardController;

$user = \Drupal::entityTypeManager()->getStorage("user")->load(1);
if ($user) {
  \Drupal::currentUser()->setAccount($user);
}

$controller = AdminDashboardController::create(\Drupal::getContainer());
$response = $controller->dashboard();
$data = json_decode($response->getContent(), TRUE);

if (!is_array($data)) {
  throw new RuntimeException("Dashboard API did not return an array.");
}

$cards = $data["cards"] ?? [];
$card_ids = array_column($cards, "id");

if (in_array("roles", $card_ids, TRUE)) {
  throw new RuntimeException("Roles card should not be shown on the main dashboard.");
}

$urls = [];
foreach ($cards as $card) {
  $urls[$card["id"]] = $card["url"];
}

if (($urls["analytics"] ?? "") !== "/admin/config/site-platform/analytics") {
  throw new RuntimeException("Analytics dashboard card must open analytics settings.");
}

if (($urls["jobs"] ?? "") !== "/admin/content?type=job") {
  throw new RuntimeException("Jobs dashboard card must open filtered job content.");
}

foreach (($data["recent"]["content"] ?? []) as $item) {
  if (($item["title"] ?? "") === "QA Only") {
    throw new RuntimeException("QA-only content leaked into default site dashboard recent content.");
  }

  foreach (["typeLabel", "updatedBy", "editUrl", "viewUrl", "changedFormatted"] as $key) {
    if (empty($item[$key])) {
      throw new RuntimeException("Recent item is missing " . $key);
    }
  }
}

echo "Dashboard UX API verified.\n";
'

ddev drush php:eval '
$display = \Drupal::service("entity_display.repository")->getFormDisplay("node", "site_page", "default");
$sites = $display->getComponent("field_sites");
if (($sites["type"] ?? "") !== "options_select") {
  throw new RuntimeException("Site Page Sites field must use select widget.");
}

$og = $display->getComponent("field_og_image");
if (($og["type"] ?? "") !== "media_library_widget") {
  throw new RuntimeException("Site Page Open Graph image must use Media Library widget.");
}

$storage = \Drupal\field\Entity\FieldStorageConfig::loadByName("node", "field_page_type");
$options = $storage ? $storage->getSetting("allowed_values") : [];
if (empty($options["standard"])) {
  throw new RuntimeException("Page type options are missing.");
}

$layout = \Drupal\field\Entity\FieldStorageConfig::loadByName("paragraph", "field_layout_variant");
$layout_options = $layout ? $layout->getSetting("allowed_values") : [];
if (empty($layout_options["default"])) {
  throw new RuntimeException("Layout variant options are missing.");
}

echo "Editor form UX config verified.\n";
'

echo "Admin UX checks passed."
