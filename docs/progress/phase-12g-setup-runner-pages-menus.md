# Phase 12G: Setup Runner Default Pages and Menus

## Completed Scope

This phase adds idempotent setup for frontend-safe default pages and page-connected menu visibility.

## Default Pages

The setup runner ensures these pages exist:

- home
- about
- careers
- contact

Each page is created or updated by page key.

## Page Fields

The runner sets these fields when they exist:

- field_page_key
- field_page_type
- field_summary

## Menu Fields

When default menus are enabled, the runner sets:

- field_show_in_header
- field_show_in_footer
- field_menu_title
- field_menu_weight

Default order:

- Home: 0
- About: 10
- Careers: 20
- Contact: 30

## Safety

The runner is idempotent.

If a page already exists with the same page key, it updates that page instead of creating a duplicate.

The runner does not delete pages.

## Manifest

Created or updated node IDs are tracked in the setup manifest state under:

- nodes

## Environment-specific Note

Do not export config after manually testing setup runs.

Created nodes and runtime setup state are environment-specific.

## Next Phase

Phase 12H should add default forms and frontend-safe form metadata setup.
