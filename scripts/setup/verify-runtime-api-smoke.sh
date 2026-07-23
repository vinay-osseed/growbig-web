#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-27-runtime-api-smoke-check.md"
  "setup/examples/sample.site.yml"
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php <<'PHP'
<?php
$routes = 'site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml';
$text = file_get_contents($routes);
if ($text === false) {
  fwrite(STDERR, "Unable to read: $routes\n");
  exit(1);
}

$pattern = '/_controller:\s*[\'\"]?\\\\Drupal\\\\site_platform_api\\\\Controller\\\\([A-Za-z0-9_]+)::/';
preg_match_all($pattern, $text, $matches);
$classes = array_values(array_unique($matches[1] ?? []));

if ($classes === []) {
  fwrite(STDERR, "No site_platform_api controllers found in routing file.\n");
  exit(1);
}

foreach ($classes as $class) {
  $file = "site-platform/web/modules/custom/site_platform_api/src/Controller/$class.php";
  if (!is_file($file)) {
    fwrite(STDERR, "Missing controller file referenced by routing: $file\n");
    exit(1);
  }
}
PHP

while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
done < <(find site-platform/web/modules/custom/site_platform_api/src/Controller -type f -name '*.php' -print0)

ddev drush en webform site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_form site_platform_content site_platform_media site_platform_setup site_platform_api site_platform_admin -y
ddev drush cr

ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml >/dev/null

base='https://site-platform.ddev.site'

fetch() {
  local label="$1"
  local path="$2"
  local outfile="$3"
  local url="$base$path"

  echo "Checking $label: $path"
  local status
  status="$(curl -ksS -o "$outfile" -w '%{http_code}' "$url")"
  if [ "$status" != "200" ] && [ "$status" != "201" ]; then
    echo "Request failed for $label"
    echo "URL: $url"
    echo "HTTP status: $status"
    cat "$outfile" || true
    echo
    exit 1
  fi
}

assert_contains() {
  local label="$1"
  local file="$2"
  local needle="$3"

  if ! grep -Fq "$needle" "$file"; then
    echo "Assertion failed for $label"
    echo "Expected to find: $needle"
    echo "File: $file"
    cat "$file" || true
    echo
    exit 1
  fi
}

tmpdir="$(mktemp -d)"
trap 'rm -rf "$tmpdir"' EXIT

fetch 'site API' '/api/v1/site?site=yamltest' "$tmpdir/site.json"
fetch 'pages index API' '/api/v1/pages?site=yamltest' "$tmpdir/pages.json"
fetch 'page detail API' '/api/v1/pages/home?site=yamltest' "$tmpdir/page-home.json"
# The route resolver resolves frontend paths. In the sample YAML, home is /home and about is /about.
# Do not call /api/v1/routes without a path here, because that resolves root / and the sample site has no root page.
fetch 'route detail API for home path' '/api/v1/routes/home?site=yamltest' "$tmpdir/route-home.json"
fetch 'route detail API for about path' '/api/v1/routes/about?site=yamltest' "$tmpdir/route-about.json"
fetch 'menus index API' '/api/v1/menus?site=yamltest' "$tmpdir/menus.json"
fetch 'menu detail API' '/api/v1/menus/main?site=yamltest' "$tmpdir/menu-main.json"
fetch 'form schema API' '/api/v1/forms/contact?site=yamltest' "$tmpdir/form-contact.json"

echo 'Checking form submit API: /api/v1/forms/contact/submit?site=yamltest'
submit_status="$(curl -ksS -X POST "$base/api/v1/forms/contact/submit?site=yamltest" -H 'Content-Type: application/json' --data '{"email":"smoke@example.com","message":"Smoke test"}' -o "$tmpdir/form-submit.json" -w '%{http_code}')"
if [ "$submit_status" != "200" ] && [ "$submit_status" != "201" ]; then
  echo 'Request failed for form submit API'
  echo "URL: $base/api/v1/forms/contact/submit?site=yamltest"
  echo "HTTP status: $submit_status"
  cat "$tmpdir/form-submit.json" || true
  echo
  exit 1
fi

fetch 'content index API' '/api/v1/content?site=yamltest' "$tmpdir/content.json"
fetch 'content detail API' '/api/v1/content/global/footer_note?site=yamltest' "$tmpdir/content-footer.json"
fetch 'media index API' '/api/v1/media?site=yamltest' "$tmpdir/media.json"
fetch 'media detail API' '/api/v1/media/hero-image?site=yamltest' "$tmpdir/media-hero.json"
fetch 'SEO index API' '/api/v1/seo?site=yamltest' "$tmpdir/seo.json"
fetch 'SEO page API' '/api/v1/seo/page/home?site=yamltest' "$tmpdir/seo-home.json"
fetch 'analytics API' '/api/v1/analytics?site=yamltest' "$tmpdir/analytics.json"
fetch 'search API' '/api/v1/search?site=yamltest&q=brochure' "$tmpdir/search.json"

assert_contains 'site API' "$tmpdir/site.json" '"siteKey":"yamltest"'
assert_contains 'pages index API' "$tmpdir/pages.json" 'YAML Test Home'
assert_contains 'page detail API components' "$tmpdir/page-home.json" '"components"'
assert_contains 'page detail API hero' "$tmpdir/page-home.json" 'YAML home hero'
assert_contains 'route detail API home title' "$tmpdir/route-home.json" 'YAML Test Home'
assert_contains 'route detail API home path' "$tmpdir/route-home.json" 'home'
assert_contains 'route detail API about content' "$tmpdir/route-about.json" 'About YAML Test'
assert_contains 'menus index API' "$tmpdir/menus.json" 'YAML Main Menu'
assert_contains 'menu detail API' "$tmpdir/menu-main.json" '"title":"Contact"'
assert_contains 'form schema API' "$tmpdir/form-contact.json" '"storage":"webform"'
assert_contains 'form submit API' "$tmpdir/form-submit.json" '"storage":"webform"'
assert_contains 'content index API' "$tmpdir/content.json" 'footer_note'
assert_contains 'content detail API key' "$tmpdir/content-footer.json" '"key":"footer_note"'
assert_contains 'content detail API summary' "$tmpdir/content-footer.json" 'YAML footer summary'
assert_contains 'media index API' "$tmpdir/media.json" 'hero-image'
assert_contains 'media detail API' "$tmpdir/media-hero.json" 'YAML hero image'
assert_contains 'SEO index API' "$tmpdir/seo.json" 'SitePageSeo'
assert_contains 'SEO page API path' "$tmpdir/seo-home.json" 'home'
# Analytics can be disabled for a local sample site. Verify the contract shape rather than forcing real IDs.
assert_contains 'analytics API field siteKey' "$tmpdir/analytics.json" '"siteKey":"yamltest"'
assert_contains 'analytics API field enabled' "$tmpdir/analytics.json" '"enabled":'
assert_contains 'analytics API field gaMeasurementId' "$tmpdir/analytics.json" '"gaMeasurementId":'
assert_contains 'analytics API field gtmContainerId' "$tmpdir/analytics.json" '"gtmContainerId":'
assert_contains 'analytics API field loadMode' "$tmpdir/analytics.json" '"loadMode":"frontend_controlled"'
assert_contains 'search API' "$tmpdir/search.json" 'YAML Brochure'

echo "Runtime API smoke verification passed."
