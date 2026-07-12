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

if (($urls["menus"] ?? "") !== "/admin/site-dashboard/menus") {
  throw new RuntimeException("Menus dashboard card must open the frontend menu dashboard.");
}

if (($urls["reusable_content"] ?? "") !== "/admin/site-dashboard/content") {
  throw new RuntimeException("Reusable Content dashboard card must open grouped reusable content.");
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

$routes = \Drupal::service("router.route_provider");
$routes->getRouteByName("site_platform_admin.frontend_menus");
$routes->getRouteByName("site_platform_admin.reusable_content");

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


ddev drush php:eval '
$content_source = \Drupal\field\Entity\FieldStorageConfig::loadByName("paragraph", "field_content_source");
$content_options = $content_source ? $content_source->getSetting("allowed_values") : [];
if (empty($content_options["services"]) || empty($content_options["partners"]) || empty($content_options["team"]) || empty($content_options["jobs"])) {
  throw new RuntimeException("Content source options are missing.");
}

$background = \Drupal\field\Entity\FieldStorageConfig::loadByName("paragraph", "field_background_style");
$background_options = $background ? $background->getSetting("allowed_values") : [];
if (empty($background_options["default"]) || empty($background_options["dark_grid"])) {
  throw new RuntimeException("Background style options are missing.");
}

$service_display = \Drupal::service("entity_display.repository")->getFormDisplay("node", "service", "default");
if ($service_display->getComponent("field_accent_color") !== NULL) {
  throw new RuntimeException("Service accent color should be hidden from editor forms.");
}

$card_display = \Drupal::service("entity_display.repository")->getFormDisplay("paragraph", "card_item", "default");
if ($card_display->getComponent("field_accent_color") !== NULL) {
  throw new RuntimeException("Card accent color should be hidden from editor forms.");
}

echo "Editor dropdown cleanup verified.\n";
'

echo "Admin UX checks passed."
