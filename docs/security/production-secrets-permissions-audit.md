# Production Secrets and Permissions Audit

This document supports production readiness review for Site Platform v2.

## Safety Rules

- Do not commit real secret values.
- Do not commit private recipient addresses.
- Do not commit production database credentials.
- Do not store API keys in YAML drafts.
- Keep environment-specific values in environment settings or an approved secret manager.

## Draft Files

    setup/security/production-secrets.audit.yml
    setup/security/production-permissions.audit.yml

These files are planning drafts only. They do not change Drupal configuration or permissions.

## Secrets Review

Before launch, confirm each item has:

- owner
- storage location
- rotation decision
- deployment path
- rollback path
- approval status

Review at minimum:

- database credentials
- Drupal hash salt
- trusted host settings
- SMTP credentials
- Webform recipient handling
- analytics IDs if managed as environment values
- CDN/storage credentials if used
- any historical production sync credentials

## Permissions Review

Before launch, confirm the final permissions for:

- administrator
- site platform administrator
- site platform editor
- Webform manager
- content editor
- anonymous user
- authenticated user

Review access to:

- Site Platform admin dashboard
- Site Platform content management
- Site Platform reports
- Webform administration
- public API endpoints
- form submission endpoint
- media metadata endpoint
- search endpoint

## Approval Rule

Nothing in these drafts should be treated as approved until the relevant owner changes `status: todo` to an approved decision in a private deployment checklist or approved ticket.
