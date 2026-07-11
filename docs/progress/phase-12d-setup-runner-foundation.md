# Phase 12D: Setup Runner Foundation

## Completed Scope

This phase adds a safe setup runner foundation.

The runner validates saved setup values and applies only non-destructive config updates.

## Added

- Setup runner service
- `/admin/site-setup/run`
- Setup run form
- Setup overview run action
- Extended setup check script

## Current Runner Behavior

The runner validates required setup fields:

- Site Name
- Site Key
- Primary Domain
- Frontend URL
- Admin URL
- API URL
- Company Name
- Primary Email
- Country

The runner applies safe config:

- `system.site` name and mail
- `site_platform_api.analytics`

The runner updates:

- `site_platform_admin.setup_status`
- `site_platform_admin.setup_manifest`

## Safety

The runner does not yet create, delete, or reset:

- pages
- menus
- forms
- jobs
- reusable content
- users
- files

## Next Phase

Phase 12E should add the setup runner entity creation plan and then begin implementing idempotent creation for default pages, menus, roles, forms, and frontend-safe defaults.
