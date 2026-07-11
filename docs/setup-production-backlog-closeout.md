# Setup Workflow Production Backlog Closeout

## Status

The setup workflow is production-ready for the current architecture.

## Completed Production Features

- Admin setup overview
- Setup wizard
- Setup runner
- Setup completion lock
- Setup unlock
- Setup status reset
- Native Drush setup commands
- Portable setup CLI helper
- YAML setup import
- YAML setup validation
- Private setup file protection
- Multi-site setup row validation and inspection
- Setup profile exposed in `GET /api/v1/site`
- Final setup workflow verification

## Multi-site / Domain Creation

Real Drupal Domain record creation is intentionally not implemented yet.

Reason:

The current project does not have a Drupal Domain module enabled. The enabled modules include core/media/node/webform and the custom Site Platform modules.

Current behavior:

- `extra_sites` rows are validated from YAML.
- duplicate site keys are rejected.
- duplicate primary domains are rejected.
- setup site rows can be inspected with:

      ./scripts/site-setup.sh sites

Production rule:

Do not create fake domain records until the project explicitly adds a domain/multisite module or a confirmed custom site entity model.

## Destructive Cleanup / Reset

Destructive cleanup is intentionally not implemented.

Reason:

Production cleanup must never delete real client data accidentally.

Current safe options:

- unlock setup:

      ./scripts/site-setup.sh unlock

- reset setup status only:

      ./scripts/site-setup.sh reset-status

These commands do not delete:

- pages
- forms
- roles
- content
- files
- submissions

Production rule:

Any destructive cleanup must be implemented only after a backup-aware deletion plan is approved.

## Setup UI Polish

The setup UI already includes dedicated styling for:

- setup overview
- setup wizard
- setup run
- complete and lock screen

Current setup UI stylesheet:

- `site-platform/web/modules/custom/site_platform_admin/css/site-setup.css`

Further UI polish is optional and not a blocker for production deployment.

## Branding Usage

Branding setup values are exposed through:

- `GET /api/v1/site`
- `setupProfile.branding`

The frontend can safely consume:

- `themeColor`
- `logo`
- `favicon`

Production rule:

Keep branding values in setup runtime state or private YAML. Do not commit production-specific branding files or private URLs unless intentional.

## Final Production Verification

Run:

    ./scripts/check-final-setup-workflow.sh
    ./scripts/verify-backend-mvp.sh
    git status

Expected result:

    Final setup workflow verified.
    Backend MVP verification passed.
    nothing to commit, working tree clean
