# Phase 12U: Real Setup Wizard Branding Fields

## Completed Scope

This phase replaces placeholder setup wizard messaging with real setup fields.

## Added Wizard Fields

- site mode
- environment
- site name
- site key
- primary domain
- frontend URL
- admin URL
- API URL
- company name
- primary email
- country
- theme color
- browser title bar text
- header logo
- header logo alt text
- footer logo
- footer logo alt text
- favicon / title bar ICO
- app / mobile icon
- default social sharing image
- footer copyright text
- setup options
- analytics settings
- additional site / brand rows

## Added Checks

- `scripts/check-site-setup-ui-forms.sh`
- final setup verification now checks setup form builds

## Site API

Setup branding files are exposed under:

- `GET /api/v1/site`
- `setupProfile.branding`

## Safety

Uploaded files are marked permanent on save.

The wizard stores setup values in Drupal State API.

No setup values are exported to config by this change.
