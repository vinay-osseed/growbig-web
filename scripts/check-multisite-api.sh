#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${BASE_URL:-https://growbig-web.ddev.site}"
export BASE_URL

echo "Checking multi-site API resolver and filtering at ${BASE_URL}..."

ddev drush php:eval '
$storage = \Drupal::entityTypeManager()->getStorage("node");

$existing = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition("type", "site_profile")
  ->condition("field_site_key", "qa_multisite")
  ->range(0, 1)
  ->execute();

if ($existing) {
  $site = $storage->load(reset($existing));
}
else {
  $site = $storage->create([
    "type" => "site_profile",
    "title" => "QA Multisite",
    "status" => 1,
    "field_site_key" => "qa_multisite",
    "field_site_short_name" => "QA Site",
    "field_is_active" => 1,
    "field_is_default" => 0,
    "field_primary_domain" => ["uri" => "https://qa.growbig-web.ddev.site"],
    "field_ui_domain" => ["uri" => "https://qa-ui.growbig-web.ddev.site"],
    "field_admin_domain" => ["uri" => "https://qa-admin.growbig-web.ddev.site"],
    "field_api_domain" => ["uri" => "https://qa-api.growbig-web.ddev.site"],
    "field_website" => ["uri" => "https://qa.growbig-web.ddev.site"],
  ]);
  $site->save();
}

$page_ids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition("type", "site_page")
  ->condition("field_page_key", "qa-only")
  ->execute();

foreach ($page_ids as $page_id) {
  $storage->load($page_id)->delete();
}

$page = $storage->create([
  "type" => "site_page",
  "title" => "QA Only",
  "status" => 1,
  "field_page_key" => "qa-only",
  "field_page_type" => "standard",
  "field_summary" => "QA multisite isolation page.",
  "field_show_in_header" => 1,
  "field_show_in_footer" => 1,
  "field_menu_title" => "QA Only",
  "field_menu_weight" => 99,
  "field_sites" => [
    ["target_id" => $site->id()],
  ],
]);
$page->save();

echo "QA multisite fixture ready.\n";
'

python3 - <<'PY'
import json
import os
import subprocess
import sys

base = os.environ["BASE_URL"].rstrip("/")

def fetch(path):
    result = subprocess.run(
        ["curl", "-sk", base + path],
        capture_output=True,
        text=True,
    )
    body = result.stdout.strip()
    try:
        payload = json.loads(body) if body else {}
    except json.JSONDecodeError:
        print(body)
        raise
    return result.returncode, payload

def http_status(path):
    result = subprocess.run(
        ["curl", "-sk", "-o", "/tmp/growbig-multisite-test.json", "-w", "%{http_code}", base + path],
        capture_output=True,
        text=True,
        check=True,
    )
    return result.stdout.strip()

_, default_site = fetch("/api/v1/site")
assert default_site["id"] == "growbig", default_site

_, qa_site = fetch("/api/v1/site?site=qa_multisite")
assert qa_site["id"] == "qa_multisite", qa_site

assert http_status("/api/v1/pages/qa-only?site=qa_multisite") == "200"
assert http_status("/api/v1/pages/qa-only?site=growbig") == "404"

_, qa_menu = fetch("/api/v1/menus/header?site=qa_multisite")
assert any(item["slug"] == "qa-only" for item in qa_menu["items"]), qa_menu

_, growbig_menu = fetch("/api/v1/menus/header?site=growbig")
assert not any(item["slug"] == "qa-only" for item in growbig_menu["items"]), growbig_menu

_, services = fetch("/api/v1/content/services?site=qa_multisite")
assert services["source"] == "services"
assert services["count"] == 0, services

print("Multi-site resolver and filtering verified.")
PY

echo "Multi-site API check passed."
