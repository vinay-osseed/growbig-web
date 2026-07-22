# Phase 36: Admin Content Model Rework

## Goal

Make the Site Platform backend feel like a usable multi-site landing-page CMS instead of a collection of scattered technical content types.

## Problem Reviewed

The previous backend foundation worked from an API/runtime perspective, but the editor experience was not acceptable:

- `/admin/content` mixed all technical node bundles together.
- Site Menu, Site Menu Item, Site Form Field, and Site Form Submission looked like primary content types even though they are compatibility wrappers.
- Site Page edit screens did not clearly expose the meaningful landing-page data model.
- Webform was the correct production form system, but the admin workflow did not make that obvious.
- The Site Platform dashboard was a technical status page, not an editor workspace.

## What Changed

This phase changes the admin direction without breaking the existing API contract.

### New Site Platform Admin Workspace

The `/admin/site-platform` page is now an editor/admin workspace with sections for:

- Sites
- Landing Pages
- Content Blocks
- Media Assets
- Forms
- Menus
- Legacy Wrappers

Each section has its own route under `/admin/site-platform/*` and appears as a child menu item under Site Platform.

### Clean Editor Listings

The new admin listings group records by purpose instead of exposing all bundles in one Drupal content table:

- `Site Platform > Sites` shows Site Profiles and domain keys.
- `Site Platform > Landing Pages` shows site, path, template, component count, and SEO readiness.
- `Site Platform > Content Blocks` shows reusable source/key content.
- `Site Platform > Media Assets` shows media metadata and active state.
- `Site Platform > Forms` places native Webforms first and keeps legacy wrappers separate.
- `Site Platform > Menus` points editors to Drupal core menus while showing legacy wrappers as compatibility data.
- `Site Platform > Legacy Wrappers` isolates technical compatibility records.

### Legacy Wrapper Labels

The following bundles are relabeled so they do not look like primary editor models:

- Legacy API Menu Wrapper
- Legacy API Menu Item Wrapper
- Legacy API Form Wrapper
- Legacy API Form Field Wrapper
- Legacy API Form Submission Fallback

They are still kept because the current API/YAML importer uses them for compatibility.

### Meaningful Edit Forms

Config display files were added for all Site Platform node bundles and Paragraph component bundles so edit screens expose the real fields instead of feeling like title-only records.

This includes edit/display configuration for:

- Site Profile
- Landing Page
- Content Block
- Media Asset Metadata
- legacy compatibility wrappers
- Hero Paragraph
- Rich Text Paragraph
- CTA Paragraph

## Important Architecture Decision

This phase does not delete compatibility bundles yet. Instead, it makes the direction clear:

- landing pages use Site Page + Paragraph components
- real forms use Drupal Webform
- long-term menus should use Drupal core menu links
- long-term binary files should use Drupal Media Library
- compatibility wrapper records remain only to preserve existing APIs and YAML import behavior

## Verification

Run:

```bash
./scripts/setup/verify-admin-content-model-rework.sh
```

Expected:

```text
Admin content model rework verification passed.
```

## Next Hardening

After this phase, the next real implementation work should be one of:

- migrate menu API to normalize Drupal core menu links
- migrate media metadata toward Drupal Media entities
- add richer Paragraph component types for real landing pages
- add role-specific permissions for the clean Site Platform admin workspace
