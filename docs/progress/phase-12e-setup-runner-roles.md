# Phase 12E: Setup Runner Role Creation

## Completed Scope

This phase adds the first safe setup-runner action.

The setup runner now idempotently ensures default platform roles exist.

## Roles

The setup runner ensures these roles exist:

- site_developer
- content_admin
- hr_manager
- form_manager
- analytics_viewer

## Safety

The runner is safe to re-run.

If a role already exists, it is reused and its label/permissions are refreshed.

The runner does not delete roles.

## Manifest

Created or refreshed roles are tracked in:

- `site_platform_admin.setup_manifest`

Under:

- `roles`

## Status

The setup status `roles` step is marked:

- completed, if default roles are enabled
- skipped, if default roles are disabled

## Environment-specific Config Note

Setup values can contain environment-specific values, such as:

- frontend URL
- admin URL
- API URL
- primary domain
- analytics IDs
- system.site name/mail during a local setup test

These values may be different on local, stage, and production.

Do not commit local setup-run exports into shared config unless those values are intentionally meant to be the default for every environment.

For production, setup values should be reviewed in the UI and should not be blindly copied from a local DDEV run.

## Not Included Yet

The runner still does not create:

- pages
- menus
- webforms
- jobs
- reusable content
- users
- files

## Next Phase

Phase 12F should add idempotent setup for frontend-safe default pages and menu visibility.
