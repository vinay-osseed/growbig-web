# Editor/Admin UX Cleanup Plan

The current backend checkpoint is API-first. The default Drupal admin UI is not the final editor workflow.

## Current State

- Site Platform content is stored as Drupal content entities.
- Admin dashboard and permissions exist.
- Default content listing is usable for technical review, but not polished for editors.

## Cleanup Tasks

Later production hardening should add:

- custom admin listings for Site Profiles
- custom admin listings for Site Pages
- custom admin listings for Menus
- custom admin listings for reusable content blocks
- custom admin listings for media assets
- Webform-oriented form management links
- filters by Site Profile
- clearer separation of demo/internal/setup records
- safer role-specific admin routes
- improved labels/help text for editors

## Recommended Priority

1. Site Profiles and Pages listing
2. Webform/form management links
3. reusable content/media listings
4. filters by site
5. demo/internal content separation
