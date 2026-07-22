# Site Platform Admin Workspace

The Site Platform admin area is the preferred editor/admin entry point for this backend.

## Main URL

```text
/admin/site-platform
```

The standard Drupal content page remains available, but it is not the recommended primary workflow because it mixes implementation bundles, compatibility wrappers, and real editor content together.

## Recommended Workflow

### 1. Sites

Use:

```text
/admin/site-platform/sites
```

This lists Site Profiles. A Site Profile represents one brand/site/domain set such as GrowBig or OSSeed.

### 2. Landing Pages

Use:

```text
/admin/site-platform/pages
```

Landing Pages are the main frontend page records. Each page belongs to one Site Profile and contains:

- page key
- slug
- frontend path
- template
- SEO title/description
- ordered Paragraph components

The frontend should consume these through:

```text
/api/v1/pages/{slug}
/api/v1/routes/{path}
```

### 3. Page Components

Landing Page structure should come from Paragraph components attached to the page, not from separate loose content nodes.

Current component bundles:

- Hero
- Rich Text
- CTA

Future component bundles can add cards, grids, testimonials, logos, stats, FAQs, and feature sections.

### 4. Content Blocks

Use:

```text
/admin/site-platform/content-blocks
```

Content Blocks hold reusable source/key content such as footer text, announcements, office information, or shared snippets.

The frontend should consume these through:

```text
/api/v1/content
/api/v1/content/{source}
/api/v1/content/{source}/{key}
```

### 5. Media Assets

Use:

```text
/admin/site-platform/media-assets
```

Media Asset Metadata records currently provide frontend-safe media references through the API.

For production hardening, binary file management should move toward Drupal Media Library, while the API can continue exposing normalized media output.

### 6. Forms

Use:

```text
/admin/site-platform/forms
```

Webform is the primary form system. Real production forms must use Drupal Webform.

The Site Platform Forms page places native Webforms first and keeps old Site Form wrappers as compatibility records only.

The frontend should consume forms through:

```text
/api/v1/forms/{form}
/api/v1/forms/{form}/submit
```

### 7. Menus

Use:

```text
/admin/site-platform/menus
```

The long-term target is Drupal core menus as the editor source of truth. Current Site Menu and Site Menu Item records are compatibility wrappers for the existing API/YAML import.

### 8. Legacy Wrappers

Use:

```text
/admin/site-platform/legacy-wrappers
```

This page isolates records that should not be treated as the main editor workflow:

- Legacy API Menu Wrapper
- Legacy API Menu Item Wrapper
- Legacy API Form Wrapper
- Legacy API Form Field Wrapper
- Legacy API Form Submission Fallback

## What This Fixes

This workspace fixes the earlier confusion where every technical bundle appeared equally important in `/admin/content`.

Editors should now start from Site Platform and use grouped sections instead of scanning the mixed Drupal content list.
