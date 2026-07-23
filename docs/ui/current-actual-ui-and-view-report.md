# Current Actual UI and View Report

## Summary

Site Platform v2 is currently a backend/API-first Drupal platform. The actual visible UI today is mostly Drupal admin UI plus the custom Site Platform admin dashboard.

The public frontend UI is not finished in this branch. The backend is ready for frontend/API integration, but final public page rendering is a separate frontend task.

## What You Can View Locally Now

Use the local DDEV site:

    https://site-platform.ddev.site

### 1. Site Platform Admin Dashboard

Open:

    https://site-platform.ddev.site/admin/site-platform

What it should show:

- Site Platform status overview
- enabled platform modules
- setup/import/reset/completion state summaries
- content/entity count summaries
- links or status rows for backend review

This dashboard is useful for technical/admin review, not final editor UX polish.

### 2. Drupal Content Admin

Open:

    https://site-platform.ddev.site/admin/content

What it shows:

- Site Profile records
- Site Page records
- Site Menu records
- Site Form wrapper records
- reusable content block records
- reusable media asset records

This is Drupal's default content listing. It works for review, but it is not the final clean editor screen.

### 3. Webform Admin UI

Open:

    https://site-platform.ddev.site/admin/structure/webform

What it shows:

- real Webforms
- Webform fields/elements
- handlers
- submissions
- confirmation settings

This is the correct place for production form fields, email handlers, spam protection, and submission management.

### 4. Public API Output

Examples:

    https://site-platform.ddev.site/api/v1/site?site=yamltest
    https://site-platform.ddev.site/api/v1/pages/home?site=yamltest
    https://site-platform.ddev.site/api/v1/routes/home?site=yamltest
    https://site-platform.ddev.site/api/v1/forms/contact?site=yamltest
    https://site-platform.ddev.site/api/v1/media?site=yamltest
    https://site-platform.ddev.site/api/v1/search?site=yamltest&q=brochure

This is what the future frontend should consume.

## What It Does Not Look Like Yet

It does not yet look like a finished public GrowBig or OSSeed website.

It does not yet have polished editor screens like:

- Manage Sites
- Manage Pages
- Manage Menus
- Manage Content Blocks
- Manage Media Assets
- Manage Forms per site

Those are planned editor/admin UX hardening tasks.

## How It Works Today

1. Setup YAML imports site-scoped records.
2. Drupal stores those records as content/entities.
3. Webform stores real form structures and submissions.
4. Custom API controllers expose stable frontend JSON endpoints.
5. The custom admin dashboard gives a technical overview.
6. The frontend will later render real public pages from API responses.

## Recommended Next UI Build Order

1. Improve Site Platform admin dashboard links.
2. Add custom admin listings for Site Profiles and Pages.
3. Add per-site filters to content listings.
4. Add Webform management shortcuts from Site Form wrapper records.
5. Add reusable content/media admin listing pages.
6. Add final editor permissions and labels.
7. Build/attach the public frontend UI.
