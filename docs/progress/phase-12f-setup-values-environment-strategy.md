# Phase 12F: Setup Values Environment Strategy

## Completed Scope

This phase separates shared setup defaults from environment-specific runtime setup values.

## Problem

Setup values can be environment-specific:

- frontend URL
- admin URL
- API URL
- primary domain
- analytics IDs
- setup status
- setup manifest

These should not be accidentally committed from local DDEV into shared config used by production.

## Decision

Setup config files remain as safe install/default values.

Runtime setup values are now stored in Drupal State API through:

- `site_platform_admin.setup_storage`

## Runtime Data Stored in State

The setup storage service stores:

- setup values
- setup status
- setup manifest

This means local/stage/prod setup runs can have different runtime values without exporting them through normal config sync.

## Config Still Used For Defaults

The existing config objects are still useful as defaults:

- `site_platform_admin.setup_values`
- `site_platform_admin.setup_status`
- `site_platform_admin.setup_manifest`

The storage service merges:

1. hardcoded safe defaults
2. config defaults
3. state runtime values

## Important Deployment Rule

Do not run `drush cex` after entering production setup values unless the exported changes are intentionally meant to become shared defaults.

The setup wizard and setup runner now use state for runtime setup data, reducing this risk.

## Not Fully Solved Yet

The runner still applies some normal Drupal config:

- `system.site`
- `site_platform_api.analytics`

Those are active environment config values. They should be treated carefully during production setup.

Environment overrides may still be preferred for deployment-specific values.

## Next Phase

Phase 12G should begin idempotent frontend-safe page and menu setup.
