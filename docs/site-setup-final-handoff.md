# Site Setup Final Handoff

## Status

The setup workflow is ready for local, stage, and production use.

## Main Admin URLs

- `/admin/site-setup`
- `/admin/site-setup/wizard`
- `/admin/site-setup/run`
- `/admin/site-setup/complete`
- `/admin/site-setup/unlock`
- `/admin/site-setup/reset-status`

## Main CLI Commands

Using helper:

    ./scripts/site-setup.sh status
    ./scripts/site-setup.sh preview
    ./scripts/site-setup.sh sites
    ./scripts/site-setup.sh import setup/site.yml
    ./scripts/site-setup.sh run
    ./scripts/site-setup.sh complete
    ./scripts/site-setup.sh unlock
    ./scripts/site-setup.sh reset-status
    ./scripts/site-setup.sh check

Using native Drush in DDEV:

    ddev drush site-platform:setup-status
    ddev drush site-platform:setup-preview
    ddev drush site-platform:setup-sites
    ddev drush site-platform:setup-import setup/site.yml
    ddev drush site-platform:setup-run
    ddev drush site-platform:setup-complete
    ddev drush site-platform:setup-unlock
    ddev drush site-platform:setup-reset-status

## Production CLI

On production or stage, set `DRUSH_BIN`:

    DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh status
    DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh preview
    DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh import setup/site.yml
    DRUSH_BIN=/var/www/html/site-platform/vendor/bin/drush ./scripts/site-setup.sh run

## Setup YAML

Committed example:

- `setup/site.example.yml`

Private environment files must not be committed:

- `setup/site.yml`
- `setup/site.local.yml`
- `setup/site.stage.yml`
- `setup/site.prod.yml`
- `setup/site.production.yml`

## Recommended Production Flow

1. Copy `setup/site.example.yml` to private `setup/site.yml`.
2. Update URLs, company info, country, setup options, and analytics values.
3. Import values:

        ./scripts/site-setup.sh import setup/site.yml

4. Preview:

        ./scripts/site-setup.sh preview

5. Run setup:

        ./scripts/site-setup.sh run

6. Verify:

        ./scripts/check-final-setup-workflow.sh

7. Complete and lock:

        ./scripts/site-setup.sh complete

## Site API

The Site API exposes setup values under:

- `setupProfile`

Endpoint:

- `GET /api/v1/site`

## Safety Notes

- Setup runtime values are stored in Drupal State API.
- Setup import does not export values to config.
- Do not run `drush cex` after importing environment-specific setup values unless intentional.
- Reset status does not delete pages, forms, roles, content, files, or submissions.
- Destructive cleanup is intentionally not included.

## Final Verification

Run:

    ./scripts/check-final-setup-workflow.sh

This checks:

- setup CLI script syntax
- setup status
- setup preview
- setup site rows
- setup foundation
- Site API setup profile
- backend MVP
- custom code standards

## Production Backlog Closeout

The production backlog closeout is documented in:

- `docs/setup-production-backlog-closeout.md`

Key decisions:

- real Domain record creation is not implemented because no Domain module is enabled
- destructive cleanup is not implemented because it requires an approved backup-aware deletion plan
- setup UI polish is production-ready for the current scope
- branding values are available through `setupProfile.branding`

## Setup Wizard Branding Fields

The setup wizard includes production-ready branding fields for:

- header logo
- footer logo
- browser title bar favicon / ICO
- app / mobile icon
- default social sharing image
- theme color
- footer copyright
- title bar text

These values are exposed through:

- `GET /api/v1/site`
- `setupProfile.branding`

## Current Production Setup Status

The backend setup workflow is ready for the current single-site architecture.

A non-technical admin can complete the setup flow after a developer has deployed the code, installed the site, and provided an admin login.

Admin setup URLs:

- `/admin/site-setup`
- `/admin/site-setup/wizard`
- `/admin/site-setup/run`
- `/admin/site-setup/complete`
- `/admin/site-dashboard`

Recommended non-technical admin flow:

1. Open `/admin/site-setup`.
2. Open the setup wizard.
3. Review or enter site identity, URLs, contact values, branding values, analytics values, and setup options.
4. Save setup values.
5. Open Prepare Run.
6. Run setup.
7. Open Complete and Lock.
8. Complete setup.
9. Use Site Dashboard to manage pages, forms, menus, media, jobs, reusable content, and analytics overview.

## Current Site Model

This project currently runs one primary site:

- GrowBig

The setup system supports `extra_sites` rows as future-ready metadata, but it does not create real Drupal Domain records yet.

Do not treat Additional Sites / Brands rows as active Drupal domains.

Future multi-site support requires one approved architecture:

- Drupal Domain module
- custom site entity/table
- decoupled-only site metadata consumed by the frontend

## Frontend Menu Source

Frontend header and footer menus are API-driven.

The source of truth is not Drupal core node edit Menu settings.

Use these APIs:

    /api/v1/menus/header
    /api/v1/menus/footer

Both should return frontend menu items such as Home, About, Careers, and Contact.

## Fresh Install vs Restored Demo Data

A fresh config install creates structure and configuration.

It does not restore previous content, media, logos, file entities, or full demo page sections unless those are included in the database/content seed.

To restore the full existing GrowBig demo data, restore both:

- database backup
- files backup

The restored database contains the media/file entity records. The files backup alone is not enough for logos and media.
