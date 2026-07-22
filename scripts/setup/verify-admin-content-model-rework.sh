#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-36-admin-content-model-rework.md"
  "docs/admin/site-platform-admin-workspace.md"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.task.yml"
  "site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"
  "site-platform/config/sync/core.entity_form_display.node.site_profile.default.yml"
  "site-platform/config/sync/core.entity_form_display.node.site_page.default.yml"
  "site-platform/config/sync/core.entity_form_display.node.site_content_block.default.yml"
  "site-platform/config/sync/core.entity_form_display.node.site_media_asset.default.yml"
  "site-platform/config/sync/core.entity_form_display.paragraph.site_hero.default.yml"
  "site-platform/config/sync/core.entity_form_display.paragraph.site_rich_text.default.yml"
  "site-platform/config/sync/core.entity_form_display.paragraph.site_cta.default.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

grep -Fq "site_platform_admin.sites" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml
grep -Fq "site_platform_admin.pages" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml
grep -Fq "site_platform_admin.forms" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml
grep -Fq "site_platform_admin.legacy_wrappers" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml

grep -Fq "function sites" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php
grep -Fq "function pages" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php
grep -Fq "function forms" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php
grep -Fq "function legacyWrappers" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php

grep -Fq "Legacy API Menu Wrapper" site-platform/config/sync/node.type.site_menu.yml
grep -Fq "Legacy API Form Wrapper" site-platform/config/sync/node.type.site_form.yml
grep -Fq "Landing Page" site-platform/config/sync/node.type.site_page.yml

grep -Fq "field_page_components" site-platform/config/sync/core.entity_form_display.node.site_page.default.yml
grep -Fq "type: paragraphs" site-platform/config/sync/core.entity_form_display.node.site_page.default.yml
grep -Fq "field_component_title" site-platform/config/sync/core.entity_form_display.paragraph.site_hero.default.yml
grep -Fq "Webform is the primary form system" docs/admin/site-platform-admin-workspace.md

echo "Admin content model rework verification passed."
