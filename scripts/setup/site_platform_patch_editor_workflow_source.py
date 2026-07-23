#!/usr/bin/env python3
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[2]

def patch_file(path, fn):
    p = ROOT / path
    text = p.read_text()
    new = fn(text)
    if new != text:
        p.write_text(new)

# 1. Fix NativeApiController JSON helper if missing.
def patch_api_controller(text: str) -> str:
    if "private function json(array $data" not in text:
        marker = "  /**\n   * Field exists check.\n   */"
        method = """  /**
   * Returns a JSON API response.
   */
  private function json(array $data, int $status = 200): JsonResponse {
    $response = new JsonResponse($data, $status);
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

"""
        if marker not in text:
            raise SystemExit("Could not find insertion point in NativeApiController.php")
        text = text.replace(marker, method + marker)
    return text

patch_file("site-platform/web/modules/custom/site_platform_native/src/Controller/NativeApiController.php", patch_api_controller)

# 2. Remove Legacy Wrappers menu link block.
def remove_menu_legacy(text: str) -> str:
    return re.sub(r"\nsite_platform_admin\.legacy_wrappers:\n(?:  .+\n)+", "\n", text)

patch_file("site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml", remove_menu_legacy)

# 3. Remove Legacy Wrappers route block.
def remove_route_legacy(text: str) -> str:
    return re.sub(r"\nsite_platform_admin\.legacy_wrappers:\n(?:  .+\n)+", "\n", text)

patch_file("site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml", remove_route_legacy)

# 4. Clean legacy wording and counts from admin controller.
def patch_admin_controller(text: str) -> str:
    # Remove the Legacy Wrappers section from the section list.
    text = re.sub(
        r"\n    'Legacy Wrappers' => \[\n      'path' => '/admin/site-platform/legacy-wrappers',\n      'purpose' => 'Technical compatibility bundles kept out of the primary editor workflow\.',\n    \],",
        "",
        text,
    )
    text = text.replace(
        "reviewing and managing sites, pages, content, media, forms, menus, and compatibility wrappers.",
        "reviewing and managing sites, domains, pages, media, forms, menus, datasets, and clean frontend APIs.",
    )
    text = text.replace(
        "reviewing sites, pages, content, media, forms, menus, and compatibility wrappers.",
        "reviewing sites, domains, pages, media, forms, menus, datasets, and APIs.",
    )
    text = text.replace(
        "'Treat legacy menu/form wrapper nodes as compatibility records only.',",
        "'Use Drupal core menus for navigation and Webform for real forms.',",
    )
    legacy_lines = [
        "    'site_menu' => 'Legacy API Menu Wrapper',\n",
        "    'site_menu_item' => 'Legacy API Menu Item Wrapper',\n",
        "    'site_form' => 'Legacy API Form Wrapper',\n",
        "    'site_form_field' => 'Legacy API Form Field Wrapper',\n",
        "    'site_form_submission' => 'Legacy API Form Submission Fallback',\n",
    ]
    for line in legacy_lines:
        text = text.replace(line, "")
    # Remove legacy bundle mapping entries from count section map.
    text = re.sub(r"\n      'site_menu' => '/admin/site-platform/legacy-wrappers',", "", text)
    text = re.sub(r"\n      'site_menu_item' => '/admin/site-platform/legacy-wrappers',", "", text)
    text = re.sub(r"\n      'site_form' => '/admin/site-platform/legacy-wrappers',", "", text)
    text = re.sub(r"\n      'site_form_field' => '/admin/site-platform/legacy-wrappers',", "", text)
    text = re.sub(r"\n      'site_form_submission' => '/admin/site-platform/legacy-wrappers',", "", text)
    return text

patch_file("site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php", patch_admin_controller)
