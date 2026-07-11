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
