# Site Setup User Guide

## Purpose

The Site Setup workflow initializes the backend and frontend-safe defaults after Drupal is installed.

It supports:

- local development
- staging deployment
- production deployment
- non-technical admin setup
- developer setup
- single-site setup
- future multi-site setup
- safe setup retry
- setup locking
- safe status reset

## Admin URLs

Setup overview:

- `/admin/site-setup`

Setup wizard:

- `/admin/site-setup/wizard`

Prepare setup run:

- `/admin/site-setup/run`

Complete and lock setup:

- `/admin/site-setup/complete`

Unlock setup:

- `/admin/site-setup/unlock`

Reset setup status only:

- `/admin/site-setup/reset-status`

## Setup Flow Summary

Recommended order:

1. Open `/admin/site-setup`.
2. Open Setup Wizard.
3. Enter required setup values.
4. Save setup values.
5. Prepare Setup Run.
6. Review created/updated setup items.
7. Complete and Lock Setup.
8. Open Site Dashboard.
9. Verify frontend APIs.

## Required Setup Values

The setup wizard requires:

- Site Name
- Site Key
- Primary Domain
- Frontend URL
- Admin URL
- API URL
- Company Name
- Primary Email
- Country

These values allow the backend and decoupled frontend to work safely.

## Optional Setup Values

These can be added during setup or later:

- theme color
- logo URL
- favicon URL
- analytics settings
- demo content
- future multi-site rows
- additional business details
- social links
- SEO defaults

## Frontend-safe Defaults

The setup workflow creates or verifies frontend-safe defaults so the decoupled frontend does not break.

The setup runner can ensure:

- default roles
- Home page
- About page
- Careers page
- Contact page
- header menu visibility
- footer menu visibility
- Contact Us form
- Job Application form
- analytics config fallback

## Default Pages

The setup runner ensures these pages exist:

- Home
- About
- Careers
- Contact

Pages are matched by page key, so rerunning setup does not create duplicates.

Default route mapping:

- Home: `/`
- About: `/about`
- Careers: `/careers`
- Contact: `/contact`

## Default Menu Visibility

When default menus are enabled, the setup runner sets page-connected menu fields:

- `field_show_in_header`
- `field_show_in_footer`
- `field_menu_title`
- `field_menu_weight`

Default order:

- Home: 0
- About: 10
- Careers: 20
- Contact: 30

## Default Forms

The setup runner ensures these webforms exist:

- `contact_us`
- `job_application`

If a form already exists, setup reuses it and does not overwrite existing fields.

Frontend form endpoints:

- `GET /api/v1/forms/contact-us`
- `POST /api/v1/forms/contact-us/submit`
- `GET /api/v1/forms/job-application`
- `POST /api/v1/forms/job-application/submit`

## Default Roles

The setup runner ensures these roles exist:

- `site_developer`
- `content_admin`
- `hr_manager`
- `form_manager`
- `analytics_viewer`

If a role already exists, setup reuses it and refreshes baseline permissions.

## Runtime State vs Config

Setup runtime values are stored in Drupal State API.

This is intentional because these values are environment-specific:

- frontend URL
- admin URL
- API URL
- primary domain
- analytics IDs
- setup status
- setup manifest

Local, stage, and production can each have different setup values.

## Important Config Export Rule

Do not run `drush cex` after entering local or production setup values unless you intentionally want those values exported as shared defaults.

Normal setup values are runtime state and should stay environment-specific.

The setup runner may still update active config values such as:

- `system.site`
- `site_platform_api.analytics`

If those differ by environment, review carefully before exporting config.

## Production Setup Workflow

Recommended production order:

1. Deploy code.
2. Run Composer install.
3. Run database updates.
4. Import shared config.
5. Clear cache.
6. Confirm backup exists.
7. Open `/admin/site-setup`.
8. Enter or review production setup values.
9. Prepare setup run.
10. Verify pages, menus, forms, roles, and APIs.
11. Complete and lock setup.
12. Open Site Dashboard.
13. Do not export environment-specific setup values unless intentional.

Suggested commands:

    ddev drush updb -y
    ddev drush cim -y
    ddev drush cr
    ./scripts/verify-backend-mvp.sh
    ./scripts/check-site-setup.sh

For non-DDEV production, use equivalent Drush commands from the Drupal root.

## Developer Local Setup Workflow

Recommended local order:

1. Start local environment.
2. Install dependencies.
3. Import config.
4. Open `/admin/site-setup`.
5. Add local URLs.
6. Prepare setup run.
7. Verify APIs.
8. Complete setup if needed.

Example local values:

- Frontend URL: `https://growbig-web.ddev.site`
- Admin URL: `https://admin.growbig-web.ddev.site`
- API URL: `https://api.growbig-web.ddev.site`

Do not export local values unless they are intended as shared defaults.

## Non-technical Admin Workflow

1. Login to Drupal.
2. Open `/admin/site-setup`.
3. Click Open Setup Wizard.
4. Fill required fields.
5. Save.
6. Click Prepare Run.
7. If no missing values are shown, continue.
8. Click Complete and Lock.
9. Open Site Dashboard.

The admin does not need terminal access for this workflow.

## Setup Lock

After setup is completed, it can be locked.

Locked setup means:

- setup is marked completed
- setup is marked locked
- verification is marked completed

Locking does not change frontend data by itself.

## Unlock Setup

Unlock setup from:

- `/admin/site-setup/unlock`

Unlocking does not delete any data.

It only changes setup runtime status:

- completed: false
- locked: false
- current step: setup_unlocked
- verification: pending

Use unlock when setup needs another run or correction.

## Reset Setup Status Only

Reset setup status from:

- `/admin/site-setup/reset-status`

This is safe and non-destructive.

It does not delete:

- setup values
- setup manifest
- pages
- menus
- forms
- roles
- content
- files
- submissions

Use this when setup failed or needs to be restarted without removing existing data.

## What Setup Does Not Delete

The current setup workflow does not delete:

- nodes
- pages
- menus
- webforms
- roles
- files
- submissions
- jobs
- reusable content

Destructive cleanup should be implemented separately with stronger confirmation and backups.

## Verification Commands

Run:

    ./scripts/check-site-setup.sh
    ./scripts/verify-backend-mvp.sh
    ./scripts/check-code.sh

Optional API checks:

    curl -k https://growbig-web.ddev.site/api/v1/pages | python3 -m json.tool
    curl -k https://growbig-web.ddev.site/api/v1/menus/header | python3 -m json.tool
    curl -k https://growbig-web.ddev.site/api/v1/forms/contact-us | python3 -m json.tool
    curl -k https://growbig-web.ddev.site/api/v1/forms/job-application | python3 -m json.tool

## Current Safe Setup Scope

Current setup can safely manage:

- setup runtime values
- setup status
- setup manifest
- default roles
- default pages
- page-connected menu visibility
- default webforms
- setup completion lock
- setup unlock
- setup status reset

## Future Scope

Future setup phases may add:

- setup-created data cleanup
- backup-aware reset
- multi-site setup rows
- setup import from YAML
- CLI setup command
- richer frontend branding defaults
- site profile API integration with setup values
