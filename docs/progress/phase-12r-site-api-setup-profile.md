# Phase 12R: Site API Setup Profile

## Completed Scope

This phase syncs setup runtime values into the Site API.

## Added

- Site API setup profile response subscriber
- `setupProfile` object in `GET /api/v1/site`
- Site API setup profile check script
- setup guide documentation

## Behavior

The Site API response now includes setup runtime values from Drupal State API.

This is a non-breaking addition.

Existing Site API fields are not removed or renamed.

## Safety

This phase does not export setup values into config.

It only exposes frontend-safe setup values through the Site API response.

## Next Phase

Phase 12S should polish Setup Wizard and Setup Run pages or add production non-DDEV CLI examples.
