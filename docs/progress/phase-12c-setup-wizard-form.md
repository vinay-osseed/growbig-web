# Phase 12C: Setup Wizard Form UI

## Completed Scope

This phase adds the first setup wizard form.

The form saves first-run setup values but does not run setup actions yet.

## Added

- Setup values config
- Setup values schema
- `/admin/site-setup/wizard`
- Setup wizard form
- Setup overview action button
- Extended setup check script

## Current Behavior

The wizard collects:

- setup mode
- environment
- site identity
- primary domain
- frontend/admin/API URLs
- contact information
- frontend-safe defaults
- setup options
- analytics values

The wizard saves values to:

- `site_platform_admin.setup_values`

The wizard updates setup status current step to:

- `setup_form_saved`

## Not Included Yet

The setup wizard does not yet create pages, menus, forms, content, or users.

That will be handled by the setup runner service in a later phase.

## Next Phase

Phase 12D should add the setup runner service.
