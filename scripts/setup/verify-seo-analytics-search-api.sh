#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_api/src/Controller/SeoApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/AnalyticsApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/SearchApiController.php"
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
  "setup/examples/sample.site.yml"
  "docs/implementation/phase-19-seo-analytics-search-api.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php -l site-platform/web/modules/custom/site_platform_api/src/Controller/SeoApiController.php >/dev/null
php -l site-platform/web/modules/custom/site_platform_api/src/Controller/AnalyticsApiController.php >/dev/null
php -l site-platform/web/modules/custom/site_platform_api/src/Controller/SearchApiController.php >/dev/null
php -l site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php >/dev/null

ddev drush en webform site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_form site_platform_content site_platform_media site_platform_setup site_platform_api -y
ddev drush cr

ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml >/dev/null

seo_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/seo?site=yamltest')"
seo_page_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/seo/page/home?site=yamltest')"
analytics_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/analytics?site=yamltest')"
search_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/search?site=yamltest&q=YAML')"
media_search_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/search?site=yamltest&q=brochure')"
missing_seo_json="$(curl -s 'https://site-platform.ddev.site/api/v1/seo/page/missing?site=yamltest')"

printf '%s' "$seo_json" | grep -q '"type":"SitePageSeo"'
printf '%s' "$seo_json" | grep -q '"siteKey":"yamltest"'
printf '%s' "$seo_page_json" | grep -q '"title":"YAML Test Home"'
printf '%s' "$seo_page_json" | grep -q '"canonicalPath":"/home"'
printf '%s' "$analytics_json" | grep -q '"enabled":true'
printf '%s' "$analytics_json" | grep -q '"gaMeasurementId":"G-YAMLTEST"'
printf '%s' "$analytics_json" | grep -q '"gtmContainerId":"GTM-YAMLTEST"'
printf '%s' "$search_json" | grep -q '"query":"YAML"'
printf '%s' "$search_json" | grep -q '"results"'
printf '%s' "$media_search_json" | grep -q '"type":"media"'
printf '%s' "$media_search_json" | grep -q 'YAML Brochure'
printf '%s' "$missing_seo_json" | grep -q '"code":"seo_page_not_found"'

echo "SEO, analytics, and search API verification passed."
