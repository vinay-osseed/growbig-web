# Phase 12S: Portable Setup CLI Helper

## Completed Scope

This phase makes the setup helper usable outside DDEV.

## Added

- portable Drush command runner in `scripts/site-setup.sh`
- `DRUSH_BIN` override support
- production/stage CLI documentation

## Behavior

The helper now uses:

1. `DRUSH_BIN` when provided
2. `ddev drush` when DDEV is available
3. `drush` as a final fallback

## Safety

This does not change setup runner behavior.

It only changes how the helper calls Drush.

## Next Phase

Phase 12T should polish Setup Wizard and Setup Run admin pages or add a setup status API endpoint.
