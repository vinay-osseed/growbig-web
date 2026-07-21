# Secrets and Permissions Checklist

This checklist should be completed before production deployment.

## Secrets

Review and rotate as needed:

- database credentials
- hash salt
- trusted host settings
- SMTP/API credentials
- analytics/container IDs if treated as managed configuration
- CDN/storage credentials if used
- any historical production sync credentials

Rules:

- Do not commit secrets to config or YAML.
- Store environment-specific values in environment settings or a secret manager.
- Rotate any secret that appeared in old logs, local files, or shared notes.

## Drupal Access

Review permissions for:

- administrator
- site platform administrator/editor
- webform manager
- content editor
- anonymous user
- authenticated user

Confirm permissions for:

- administer site platform
- manage site platform content
- view site platform reports
- administer webform
- create/edit/delete content types used by Site Platform
- access API endpoints if restricted later

## API Access Policy

Confirm which endpoints are public:

- site metadata
- pages
- routes
- menus
- forms schema
- form submit
- reusable content
- media metadata
- SEO metadata
- analytics metadata
- search

Confirm whether any endpoint needs token, domain restriction, or rate limiting before launch.
